<?php

namespace App\Filament\Tenant\Resources\Branches\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\Branches\BranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    use HasPermission;
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
