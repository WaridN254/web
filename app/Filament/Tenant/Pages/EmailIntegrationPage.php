<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\EmailAccount;
use App\Services\Email\EmailAccountService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailIntegrationPage extends Page
{
    use HasPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 0;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.communication');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.email_settings');
    }


    protected static ?string $title = 'Email Integration';

    protected string $view = 'filament.tenant.pages.email-integration-page';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('can_manage_email_accounts') ?? false;
    }

    // Provider selection
    public string $selectedProvider = 'gmail';

    // Account info
    public string $accountName = '';
    public string $accountEmail = '';
    public string $accountDisplayName = '';

    // SMTP
    public string $smtpHost = '';
    public int $smtpPort = 587;
    public string $smtpEncryption = 'tls';
    public string $smtpUsername = '';
    public string $smtpPassword = '';

    // IMAP
    public bool $imapEnabled = true;
    public string $imapHost = '';
    public int $imapPort = 993;
    public string $imapEncryption = 'ssl';
    public string $imapUsername = '';
    public string $imapPassword = '';
    public bool $smtpPasswordSet = false;
    public bool $imapPasswordSet = false;

    // State
    public string $testResult = '';
    public bool $testSuccess = false;
    public bool $testing = false;
    public string $saveResult = '';
    public bool $saveSuccess = false;

    // Existing accounts
    public array $existingAccounts = [];
    public ?string $editingAccountId = null;

    // Provider presets
    public static array $providers = [
        'gmail' => [
            'name' => 'Gmail',
            'icon' => 'heroicon-o-envelope',
            'color' => '#ea4335',
            'description' => 'Use your Gmail account with App Password',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'imap_host' => 'imap.gmail.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'instructions' => 'Go to myaccount.google.com → Security → 2-Step Verification → App passwords. Generate an app password and use it below. Regular Gmail passwords will NOT work.',
        ],
        'outlook' => [
            'name' => 'Outlook / Office 365',
            'icon' => 'heroicon-o-document-text',
            'color' => '#0078d4',
            'description' => 'Microsoft Outlook, Hotmail, or Office 365',
            'smtp_host' => 'smtp.office365.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'imap_host' => 'outlook.office365.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'instructions' => 'Use your full email address as username. For Office 365, you may need to enable SMTP AUTH in the admin center.',
        ],
        'yahoo' => [
            'name' => 'Yahoo Mail',
            'icon' => 'heroicon-o-at-arrow',
            'color' => '#6001d2',
            'description' => 'Yahoo Mail with app password',
            'smtp_host' => 'smtp.mail.yahoo.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'imap_host' => 'imap.mail.yahoo.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'instructions' => 'Go to Account Info → Account Security → Generate app password. Use the app password below, not your regular Yahoo password.',
        ],
        'zoho' => [
            'name' => 'Zoho Mail',
            'icon' => 'heroicon-o-mail',
            'color' => '#e42527',
            'description' => 'Zoho Mail business email',
            'smtp_host' => 'smtp.zoho.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'imap_host' => 'imap.zoho.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'instructions' => 'Use your Zoho email and password. For two-factor auth enabled accounts, use an app-specific password.',
        ],
        'custom' => [
            'name' => 'Custom SMTP',
            'icon' => 'heroicon-o-server-stack',
            'color' => '#6b7280',
            'description' => 'Configure any SMTP/IMAP server',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'imap_host' => '',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'instructions' => 'Enter your email provider\'s SMTP and IMAP server details. Contact your email provider if you\'re unsure.',
        ],
    ];

    protected function getTenantId(): ?string
    {
        return auth()->user()?->tenant_id;
    }

    public function mount(): void
    {
        $this->loadExistingAccounts();
        $this->applyProviderPreset();
    }

    public function selectProvider(string $provider): void
    {
        $this->selectedProvider = $provider;
        if (!$this->editingAccountId) {
            $this->applyProviderPreset();
        }
        $this->testResult = '';
        $this->saveResult = '';
    }

    private function applyProviderPreset(): void
    {
        $preset = self::$providers[$this->selectedProvider] ?? self::$providers['custom'];
        $this->smtpHost = $preset['smtp_host'];
        $this->smtpPort = $preset['smtp_port'];
        $this->smtpEncryption = $preset['smtp_encryption'];
        $this->imapHost = $preset['imap_host'];
        $this->imapPort = $preset['imap_port'];
        $this->imapEncryption = $preset['imap_encryption'];
    }

    public function loadExistingAccounts(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $this->existingAccounts = EmailAccount::where('tenant_id', $tenantId)
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'email_address' => $a->email_address,
                'display_name' => $a->display_name ?? $a->email_address,
                'provider' => $a->provider ?? 'custom',
                'connection_status' => $a->connection_status,
                'is_active' => $a->is_active,
                'smtp_host' => $a->smtp_host,
                'last_synced_at' => $a->last_synced_at?->diffForHumans(),
            ])
            ->toArray();
    }

    public function editExistingAccount(string $accountId): void
    {
        $tenantId = $this->getTenantId();
        $account = EmailAccount::where('tenant_id', $tenantId)->find($accountId);
        if (!$account) return;

        $this->editingAccountId = $accountId;
        $this->accountName = $account->display_name ?? $account->email_address;
        $this->accountEmail = $account->email_address;
        $this->accountDisplayName = $account->display_name ?? '';

        // Detect provider from SMTP host
        $host = $account->smtp_host ?? '';
        $this->selectedProvider = match(true) {
            str_contains($host, 'gmail') => 'gmail',
            str_contains($host, 'office365') || str_contains($host, 'outlook') => 'outlook',
            str_contains($host, 'yahoo') => 'yahoo',
            str_contains($host, 'zoho') => 'zoho',
            default => 'custom',
        };

        $this->smtpHost = $account->smtp_host ?? '';
        $this->smtpPort = (int) ($account->smtp_port ?? 587);
        $this->smtpEncryption = $account->smtp_encryption ?? 'tls';
        $this->smtpUsername = $account->smtp_username ?? '';
        $this->smtpPassword = '';

        $this->imapHost = $account->imap_host ?? '';
        $this->imapPort = (int) ($account->imap_port ?? 993);
        $this->imapEncryption = $account->imap_encryption ?? 'ssl';
        $this->imapUsername = $account->imap_username ?? '';
        $this->imapPassword = '';

        $this->imapEnabled = filled($account->imap_host);

        // Track if passwords exist (encrypted, can't decrypt to show)
        $this->smtpPasswordSet = filled($account->smtp_password);
        $this->imapPasswordSet = filled($account->imap_password);

        $this->testResult = '';
        $this->saveResult = '';

        $this->dispatch('accountLoaded');
        $this->dispatch('scrollToForm');
    }

    public function deleteExistingAccount(string $accountId): void
    {
        $tenantId = $this->getTenantId();
        EmailAccount::where('tenant_id', $tenantId)->where('id', $accountId)->update(['is_active' => false]);
        $this->loadExistingAccounts();

        if ($this->editingAccountId === $accountId) {
            $this->resetForm();
        }
    }

    public function testConnection(): void
    {
        $this->testing = true;
        $this->testResult = '';

        try {
            $existing = $this->editingAccountId
                ? EmailAccount::find($this->editingAccountId)
                : null;

            $account = new EmailAccount();
            $account->smtp_host = $this->smtpHost;
            $account->smtp_port = $this->smtpPort;
            $account->smtp_encryption = $this->smtpEncryption;
            $account->smtp_username = $this->smtpUsername ?: $this->accountEmail;

            if (filled($this->smtpPassword)) {
                $account->smtp_password = Crypt::encryptString($this->smtpPassword);
            } elseif ($existing) {
                $account->smtp_password = $existing->smtp_password ?? '';
            } else {
                $account->smtp_password = '';
            }

            $service = new EmailAccountService();
            $smtpResult = $service->testSmtpConnection($account);

            $imapResult = ['success' => true, 'message' => 'Skipped (IMAP not configured)'];
            if ($this->imapEnabled && filled($this->imapHost)) {
                $account->imap_host = $this->imapHost;
                $account->imap_port = $this->imapPort;
                $account->imap_encryption = $this->imapEncryption;
                $account->imap_username = $this->imapUsername ?: $this->accountEmail;

                if (filled($this->imapPassword)) {
                    $account->imap_password = Crypt::encryptString($this->imapPassword);
                } elseif ($existing) {
                    $account->imap_password = $existing->imap_password ?? '';
                } else {
                    $account->imap_password = '';
                }

                $imapResult = $service->testImapConnection($account);
            }

            $parts = [];
            $parts[] = $smtpResult['success'] ? "✅ SMTP: {$smtpResult['message']}" : "❌ SMTP: {$smtpResult['message']}";
            $parts[] = $imapResult['success'] ? "✅ IMAP: {$imapResult['message']}" : "❌ IMAP: {$imapResult['message']}";

            $this->testSuccess = $smtpResult['success'];
            $this->testResult = implode("\n", $parts);
        } catch (\Exception $e) {
            $this->testSuccess = false;
            $this->testResult = "❌ Error: " . $e->getMessage();
            Log::error('Email connection test failed', ['error' => $e->getMessage()]);
        }

        $this->testing = false;
    }

    public function saveAccount(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $this->saveResult = '';

        if (empty($this->accountEmail)) {
            $this->saveSuccess = false;
            $this->saveResult = 'Please enter an email address.';
            return;
        }

        if (empty($this->smtpHost)) {
            $this->saveSuccess = false;
            $this->saveResult = 'Please enter SMTP host.';
            return;
        }

        $data = [
            'email_address' => $this->accountEmail,
            'display_name' => $this->accountDisplayName ?: $this->accountEmail,
            'provider' => $this->selectedProvider,
            'smtp_host' => $this->smtpHost,
            'smtp_port' => $this->smtpPort,
            'smtp_encryption' => $this->smtpEncryption,
            'smtp_username' => $this->smtpUsername ?: $this->accountEmail,
            'imap_host' => $this->imapEnabled ? $this->imapHost : null,
            'imap_port' => $this->imapPort,
            'imap_encryption' => $this->imapEncryption,
            'imap_username' => $this->imapUsername ?: $this->accountEmail,
            'is_active' => true,
            'sync_enabled' => true,
            'connection_status' => 'connected',
        ];

        if (filled($this->smtpPassword)) {
            $data['smtp_password'] = Crypt::encryptString($this->smtpPassword);
        }
        if ($this->imapEnabled && filled($this->imapPassword)) {
            $data['imap_password'] = Crypt::encryptString($this->imapPassword);
        }

        try {
            if ($this->editingAccountId) {
                $updateData = collect($data)->except(['name'])->toArray();
                if (!isset($updateData['smtp_password']) || $updateData['smtp_password'] === null) {
                    unset($updateData['smtp_password']);
                }
                if (!isset($updateData['imap_password']) || $updateData['imap_password'] === null) {
                    unset($updateData['imap_password']);
                }
                EmailAccount::where('tenant_id', $tenantId)
                    ->where('id', $this->editingAccountId)
                    ->update($updateData);

                $this->saveSuccess = true;
                $this->saveResult = "Account updated successfully!";
            } else {
                $data['id'] = Str::uuid();
                $data['tenant_id'] = $tenantId;
                $data['user_id'] = auth()->id();
                EmailAccount::create($data);

                $this->saveSuccess = true;
                $this->saveResult = "Account created successfully!";
            }

            $this->loadExistingAccounts();
            $this->resetForm();
        } catch (\Exception $e) {
            $this->saveSuccess = false;
            $this->saveResult = "Error: " . $e->getMessage();
            Log::error('Failed to save email account', ['error' => $e->getMessage()]);
        }
    }

    public function resetForm(): void
    {
        $this->editingAccountId = null;
        $this->accountName = '';
        $this->accountEmail = '';
        $this->accountDisplayName = '';
        $this->smtpUsername = '';
        $this->smtpPassword = '';
        $this->imapUsername = '';
        $this->imapPassword = '';
        $this->smtpPasswordSet = false;
        $this->imapPasswordSet = false;
        $this->testResult = '';
        $this->saveResult = '';
        $this->applyProviderPreset();
    }

    public function toggleImap(): void
    {
        $this->imapEnabled = !$this->imapEnabled;
    }
}
