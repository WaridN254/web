<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Pages\Concerns\PrintsProductLabels;
use Filament\Actions\Action;
use Filament\Pages\Page;

class PrintQrCodesPage extends Page
{
    use HasPermission;
    use PrintsProductLabels;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?int $navigationSort = 46;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.inventory');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.print_qr_code');
    }


    protected static ?string $title = 'Print QR Code';

    public ?string $subheading = 'Manage your QR code';

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

    protected string $view = 'filament.tenant.pages.product-qr-print';
}
