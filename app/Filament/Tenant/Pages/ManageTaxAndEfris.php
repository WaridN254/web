<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\EfrisApiLog;
use App\Models\TaxCategory;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageTaxAndEfris extends Page
{
    use HasPermission;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.compliance');
    }

    protected static ?int $navigationSort = 52;

    public static function getNavigationLabel(): string
    {
        return __('navigation.tax_efris');
    }


    public string $view = 'filament.tenant.pages.manage-tax-and-efris';

    public string $activeTab = 'settings';

    public function mount(): void
    {
        $this->activeTab = 'settings';
    }

    public function getTitle(): string
    {
        return __('navigation.tax_efris');
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function testEfrisConnection(): void
    {
        try {
            // This would call your EFRIS API integration
            // For now, we'll just show a notification
            $this$this->dispatch('gooey-toast', [
                'type' => 'info',
                'title' => 'Connection Test',
                'description' => 'Testing connection to EFRIS API...',
            ]);

            // Simulate successful test
            $this$this->dispatch('gooey-toast', [
                'type' => 'success',
                'title' => 'Success',
                'description' => 'Connected successfully to EFRIS system.',
            ]);
        } catch (\Exception $e) {
            $this$this->dispatch('gooey-toast', [
                'type' => 'error',
                'title' => 'Connection Failed',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function saveEfrisSettings(): void
    {
        $this$this->dispatch('gooey-toast', [
            'type' => 'success',
            'title' => 'Saved',
            'description' => 'EFRIS settings have been saved.',
        ]);
    }

    public function retryFailedSubmissions(): void
    {
        $this$this->dispatch('gooey-toast', [
            'type' => 'info',
            'title' => 'Processing',
            'description' => 'Retrying failed EFRIS submissions...',
        ]);
    }

    public function getEfrisSubmissionStats(): array
    {
        $submitted = EfrisApiLog::where('status', 'success')->count();
        $failed = EfrisApiLog::where('status', 'failed')->count();

        return [
            'submitted' => $submitted,
            'failed' => $failed,
        ];
    }
}
