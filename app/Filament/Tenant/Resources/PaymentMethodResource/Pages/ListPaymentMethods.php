<?php

namespace App\Filament\Tenant\Resources\PaymentMethodResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMethods extends ListRecords
{
    use HasPermission;
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}
