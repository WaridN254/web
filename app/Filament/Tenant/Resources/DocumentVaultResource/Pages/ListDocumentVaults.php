<?php

namespace App\Filament\Tenant\Resources\DocumentVaultResource\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\DocumentVaultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentVaults extends ListRecords
{
    use HasPermission;
    protected static string $resource = DocumentVaultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }
}
