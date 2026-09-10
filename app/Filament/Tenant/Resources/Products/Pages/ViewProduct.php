<?php

namespace App\Filament\Tenant\Resources\Products\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Products\ProductResource;
use App\Filament\Tenant\Resources\Products\Schemas\ProductInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewProduct extends ViewRecord
{
    use HasPermission;
    protected static string $resource = ProductResource::class;

    public function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->url(fn (): string => $this->getResource()::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}