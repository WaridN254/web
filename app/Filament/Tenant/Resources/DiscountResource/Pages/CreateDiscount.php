<?php

namespace App\Filament\Tenant\Resources\DiscountResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    use HasPermission;
    protected static string $resource = DiscountResource::class;


    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $this$this->dispatch('gooey-toast', ['type' => 'success', 'title' => 'Created successfully']);
        return null;
    }
}
