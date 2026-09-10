<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Tenant\Pages\AdminDashboard;
use App\Filament\Tenant\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TenantPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();

        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Filament\Tenant\Auth\DashboardAwareLoginResponse::class,
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('tenant')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->font('Inter')
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->sidebarWidth('15.625rem')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <link rel="stylesheet" href="{{ asset(\'css/tenant-theme.css\') }}">
                    <link rel="stylesheet" href="{{ asset(\'css/gooey-toast.css\') }}">
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): string => \Illuminate\Support\Facades\Blade::render(view('partials.pwa')->render())
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => view('filament.tenant.sidebar-footer')->render()
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_LOGO_AFTER,
                fn (): string => '<div x-show="! $store.sidebar.isOpen" x-cloak class="pos-collapsed-logo" style="display:flex;justify-content:center;padding:8px 0;">
                    <svg width="28" height="28" viewBox="0 0 44 44" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="color:var(--orange, #ff7a18);">
                        <path d="M14 17v-3.5C14 8.3 17.6 5 22 5s8 3.3 8 8.5V17" />
                        <path d="M10 17h24l2.2 19.2A3.5 3.5 0 0 1 32.8 40H11.2a3.5 3.5 0 0 1-3.4-3.8L10 17Z" />
                        <path d="M17 24h10" />
                        <path d="M17 30h7" />
                    </svg>
                </div>'
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::TOPBAR_START,
                fn (): string => view('filament.tenant.topbar')->render()
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => view('filament.tenant.lock-screen')->render()
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => '<script src="' . asset('js/gooey-toast.js') . '"></script>'
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => view('partials.toast-listener')->render()
            )
            ->brandLogo(fn () => view('filament.tenant.logo'))
            ->brandLogoHeight('48px')
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: 'App\Filament\Tenant\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\Filament\Tenant\Pages')
            ->navigationGroups([
                NavigationGroup::make(fn () => __('navigation.inventory')),
                NavigationGroup::make(fn () => __('navigation.sales')),
                NavigationGroup::make(fn () => __('navigation.catalog')),
                NavigationGroup::make(fn () => __('navigation.stock')),
                NavigationGroup::make(fn () => __('navigation.purchasing')),
                NavigationGroup::make(fn () => __('navigation.customers')),
                NavigationGroup::make(fn () => __('navigation.reports')),
                NavigationGroup::make(fn () => __('navigation.compliance')),
                NavigationGroup::make(fn () => __('navigation.communication')),
                NavigationGroup::make(fn () => __('navigation.branch_management')),
                NavigationGroup::make(fn () => __('navigation.configuration')),
                NavigationGroup::make(fn () => __('navigation.administration')),
                NavigationGroup::make(fn () => __('navigation.settings')),
            ])
            ->pages([
                AdminDashboard::class,
                \App\Filament\Tenant\Pages\ProductSerialsPage::class,
                \App\Filament\Tenant\Pages\ProductsSerialsPage::class,
                \App\Filament\Tenant\Pages\VariantAttributesPage::class,
                \App\Filament\Tenant\Pages\ProductVariantsPage::class,
            ])
            ->navigationItems([
                NavigationItem::make(fn () => __('navigation.dashboard'))
                    ->icon('heroicon-o-home')
                    ->sort(0)
                    ->url(null)
                    ->visible(fn (): bool => (bool) (auth()->user()?->hasPermission('can_access_admin_dashboard') || auth()->user()?->hasPermission('can_access_sales_dashboard') || auth()->user()?->hasPermission('can_access_dashboard_2'))),
                NavigationItem::make(fn () => __('navigation.admin_dashboard'))
                    ->icon('heroicon-o-home')
                    ->parentItem(fn () => __('navigation.dashboard'))
                    ->url('/tenant')
                    ->sort(1)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_access_admin_dashboard')),
                NavigationItem::make(fn () => __('navigation.admin_dashboard_2'))
                    ->icon('heroicon-o-squares-2x2')
                    ->parentItem(fn () => __('navigation.dashboard'))
                    ->url('/tenant/admin-dashboard-2')
                    ->sort(2)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_access_dashboard_2')),
                NavigationItem::make(fn () => __('navigation.sales_dashboard'))
                    ->icon('heroicon-o-presentation-chart-line')
                    ->parentItem(fn () => __('navigation.dashboard'))
                    ->url('/tenant/sales-dashboard')
                    ->sort(3)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_access_sales_dashboard')),
                NavigationItem::make(fn () => __('navigation.sales'))
                    ->icon('heroicon-o-shopping-bag')
                    ->group(fn () => __('navigation.sales'))
                    ->sort(1)
                    ->childItems([
                        NavigationItem::make(fn () => __('navigation.sales_history'))
                            ->icon('heroicon-o-computer-desktop')
                            ->url('/tenant/sales-page')
                            ->sort(1),
                    ]),
                NavigationItem::make(fn () => __('navigation.invoices'))
                    ->icon('heroicon-o-document-text')
                    ->url('/tenant/invoice-page')
                    ->group(fn () => __('navigation.sales'))
                    ->sort(2)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_view_sales_history')),
                NavigationItem::make(fn () => __('navigation.sales_return'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url('/tenant/sales-return-page')
                    ->group(fn () => __('navigation.sales'))
                    ->sort(3)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_process_refunds')),
                NavigationItem::make(fn () => __('navigation.quotations'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url('/tenant/quotation-page')
                    ->group(fn () => __('navigation.sales'))
                    ->sort(4)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_quotations')),
                NavigationItem::make(fn () => __('navigation.pos'))
                    ->icon('heroicon-o-computer-desktop')
                    ->url('/tenant/sales-terminal')
                    ->group(fn () => __('navigation.sales'))
                    ->sort(5),
                NavigationItem::make(fn () => __('navigation.stock'))
                    ->icon('heroicon-o-archive-box')
                    ->group(fn () => __('navigation.stock'))
                    ->sort(2)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_stock'))
                    ->childItems([
                        NavigationItem::make(fn () => __('navigation.stock_dashboard'))
                            ->icon('heroicon-o-chart-bar-square')
                            ->url('/tenant/stock-dashboard')
                            ->group(fn () => __('navigation.stock'))
                            ->sort(1)
                            ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_stock')),
                        NavigationItem::make(fn () => __('navigation.stock_ledger'))
                            ->icon('heroicon-o-arrows-right-left')
                            ->url('/tenant/stock-movements')
                            ->group(fn () => __('navigation.stock'))
                            ->sort(2)
                            ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_stock')),
                        NavigationItem::make(fn () => __('navigation.stock_audits'))
                            ->icon('heroicon-o-clipboard-document-check')
                            ->url('/tenant/stock-audits')
                            ->group(fn () => __('navigation.stock'))
                            ->sort(3)
                            ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_stock')),
                    ]),
              
                NavigationItem::make(fn () => __('navigation.print_barcode'))
                    ->icon('heroicon-o-printer')
                    ->url('/tenant/print-barcodes-page')
                    ->group(fn () => __('navigation.inventory'))
                    ->sort(11)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_products')),
                NavigationItem::make(fn () => __('navigation.print_qr_code'))
                    ->icon('heroicon-o-qr-code')
                    ->url('/tenant/print-qr-codes-page')
                    ->group(fn () => __('navigation.inventory'))
                    ->sort(12)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_products')),
                NavigationItem::make(fn () => __('navigation.variant_attributes'))
                    ->icon('heroicon-o-tag')
                    ->url('/tenant/variant-attributes-page')
                    ->group(fn () => __('navigation.inventory'))
                    ->sort(13)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_variants')),
                NavigationItem::make(fn () => __('navigation.product_variants'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->url('/tenant/product-variants-page')
                    ->group(fn () => __('navigation.inventory'))
                    ->sort(14)
                    ->visible(fn (): bool => (bool) auth()->user()?->hasPermission('can_manage_variants')),

            ])
            ->widgets([
                Widgets\TenantWelcomeWidget::class,
                Widgets\TenantStatsWidget::class,
                Widgets\TenantRevenueAnalysisWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocalization::class,
                \App\Http\Middleware\EnsureOnboardingComplete::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
