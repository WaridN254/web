<?php

namespace App\Filament\Tenant\Resources\StockAudits\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Filament\Tenant\Resources\StockAudits\StockAuditResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockAudits extends ListRecords
{
    use HasPermission;
    protected static string $resource = StockAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('startStockTake')
                ->label('Start New Stock Take')
                ->icon('heroicon-o-plus')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Start New Stock Take')
                ->modalDescription('This will snapshot your current inventory levels. Are you sure you want to start a physical count?')
                ->action(function () {
                    $tenantId = auth()->user()->tenant_id;
                    $branchId = app(\App\Services\BranchService::class)->getActiveBranchId();
                    
                    // Check if there is an active audit
                    $activeAudit = \App\Models\StockAudit::where('tenant_id', $tenantId)
                        ->where('status', 'active')
                        ->first();
                        
                    if ($activeAudit) {
                        $this$this->dispatch('gooey-toast', ['type' => 'error', 'title' => 'An audit is already active']);
                        return redirect(\App\Filament\Tenant\Resources\StockAudits\StockAuditResource::getUrl('edit', ['record' => $activeAudit->id]));
                    }

                    $audit = \App\Models\StockAudit::create([
                        'tenant_id' => $tenantId,
                        'auditor_id' => auth()->id(),
                        'start_date' => now(),
                        'status' => 'active',
                        'branch_id' => $branchId,
                    ]);

                    $products = \App\Models\Product::where('tenant_id', $tenantId)->get();
                    
                    foreach ($products as $product) {
                        \App\Models\StockAuditItem::create([
                            'tenant_id' => $tenantId,
                            'audit_id' => $audit->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'system_qty' => $product->current_stock,
                            'physical_qty' => 0,
                            'discrepancy' => 0,
                            'branch_id' => $branchId,
                        ]);
                    }

                    return redirect(\App\Filament\Tenant\Resources\StockAudits\StockAuditResource::getUrl('edit', ['record' => $audit->id]));
                }),
        ];
    }
}
