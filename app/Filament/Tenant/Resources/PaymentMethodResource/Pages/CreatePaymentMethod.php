<?php

namespace App\Filament\Tenant\Resources\PaymentMethodResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    use HasPermission;
    protected static string $resource = PaymentMethodResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
