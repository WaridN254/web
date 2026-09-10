<?php

namespace App\Providers;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\Language;
use App\Services\BranchService;
use App\Services\CurrencyService;
use App\Services\LocalizationService;
use App\Services\ScannerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private static array $langFlags = [
        'en' => 'us', 'fr' => 'fr', 'es' => 'es', 'pt' => 'br', 'de' => 'de',
        'it' => 'it', 'nl' => 'nl', 'ar' => 'sa', 'zh' => 'cn', 'ja' => 'jp',
        'ko' => 'kr', 'ru' => 'ru', 'hi' => 'in', 'tr' => 'tr',
        'sw' => 'ke', 'lg' => 'ug', 'zu' => 'za', 'am' => 'et',
        'ha' => 'ng', 'yo' => 'ng', 'ig' => 'ng',
    ];

    public function register(): void
    {
        $this->app->singleton(BranchService::class, function ($app) {
            return new BranchService();
        });

        $this->app->singleton(ScannerService::class, function ($app) {
            return new ScannerService();
        });
    }

    public function boot(): void
    {
        $this->registerBladeHelpers();
        $this->registerTopbarComposer();
    }

    private function registerBladeHelpers(): void
    {
        Blade::directive('localize', function (string $expression) {
            return "<?php echo app(LocalizationService::class)->trans({$expression}); ?>";
        });

        Blade::directive('currency', function (string $expression) {
            return "<?php echo app(CurrencyService::class)->format({$expression}); ?>";
        });

        Blade::directive('dir', function () {
            return "<?php echo app(LocalizationService::class)->getDirection() === 'rtl' ? 'rtl' : 'ltr'; ?>";
        });
    }

    private function registerTopbarComposer(): void
    {
        View::composer('filament.tenant.topbar', function ($view) {
            $user = Auth::user();
            if (!$user) {
                return $view->with('unreadEmailCount', 0)
                    ->with('latestUnreadEmails', [])
                    ->with('availableLanguages', [])
                    ->with('currentLangCode', 'en')
                    ->with('currentLangFlag', 'us');
            }

            $tenantId = $user->tenant_id;
            $currentLangCode = $user->language ?? $user?->tenant?->default_language ?? 'en';
            $currentLangFlag = self::$langFlags[$currentLangCode] ?? 'un';

            $availableLanguages = Language::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->filter(fn ($l) => is_dir(resource_path('lang/' . $l->code)) || is_dir(base_path('lang/' . $l->code)))
                ->values()
                ->map(fn ($l) => [
                    'code' => $l->code,
                    'name' => $l->name,
                    'native_name' => $l->native_name,
                    'flag' => self::$langFlags[$l->code] ?? 'un',
                    'direction' => $l->direction,
                ])
                ->toArray();

            if (!$tenantId) {
                return $view->with('unreadEmailCount', 0)
                    ->with('latestUnreadEmails', [])
                    ->with('availableLanguages', $availableLanguages)
                    ->with('currentLangCode', $currentLangCode)
                    ->with('currentLangFlag', $currentLangFlag);
            }

            $canViewEmail = $user->hasPermission('can_view_email');

            $branchService = app(\App\Services\BranchService::class);
            $availableBranches = $branchService->getUserBranches();
            $activeBranch = $branchService->getActiveBranch();

            $unreadEmailCount = 0;
            $latestUnreadEmails = [];

            if ($canViewEmail) {
                $cacheKey = "tenant:{$tenantId}:topbar_email";
                $emailData = Cache::remember($cacheKey, 60, function () use ($tenantId) {
                    $unreadCount = EmailAccount::where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->sum('unread_count');

                    $latestUnread = Email::where('tenant_id', $tenantId)
                        ->where('folder', 'inbox')
                        ->where('is_read', false)
                        ->orderByDesc('received_at')
                        ->limit(5)
                        ->get()
                        ->map(fn ($e) => [
                            'id' => $e->id,
                            'from_name' => $e->from_name ?? $e->from_address,
                            'subject' => $e->subject,
                            'received_at' => $e->received_at?->diffForHumans() ?? '',
                            'body_preview' => \Illuminate\Support\Str::limit(strip_tags($e->body_html ?? $e->body_text ?? ''), 60),
                        ])
                        ->toArray();

                    return [
                        'unreadEmailCount' => $unreadCount,
                        'latestUnreadEmails' => $latestUnread,
                    ];
                });

                $unreadEmailCount = $emailData['unreadEmailCount'];
                $latestUnreadEmails = $emailData['latestUnreadEmails'];
            }

            $view->with('unreadEmailCount', $unreadEmailCount)
                 ->with('latestUnreadEmails', $latestUnreadEmails)
                 ->with('availableLanguages', $availableLanguages)
                 ->with('currentLangCode', $currentLangCode)
                 ->with('currentLangFlag', $currentLangFlag)
                 ->with('availableBranches', $availableBranches)
                 ->with('activeBranch', $activeBranch);
        });
    }
}
