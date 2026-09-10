<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Pages\Concerns\PrintsProductLabels;
use Filament\Actions\Action;
use Filament\Pages\Page;

class PrintBarcodesPage extends Page
{
    use HasPermission;
    use PrintsProductLabels;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-printer';

    protected static ?int $navigationSort = 45;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.print_barcode');
    }


    protected static ?string $title = 'Print Barcode';

    public ?string $subheading = 'Manage your barcodes';

    public function getHeaderActions(): array
    {
        return [
            Action::make('resetAll')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Reset')
                ->color('gray')
                ->modal(false)
                ->action(fn () => $this->resetAll()),
            Action::make('collapse')
                ->icon('heroicon-o-minus')
                ->tooltip('Collapse')
                ->color('gray')
                ->disabled(),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected string $view = 'filament.tenant.pages.product-barcode-print';
}
