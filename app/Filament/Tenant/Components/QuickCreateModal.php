<?php

namespace App\Filament\Tenant\Components;

use Illuminate\View\Component;

class QuickCreateModal extends Component
{
    public array $quickActions = [
        [
            'label' => 'Category',
            'icon' => 'heroicon-o-tag',
            'url' => '/tenant/categories/create',
        ],
        [
            'label' => 'Product',
            'icon' => 'heroicon-o-cube',
            'url' => '/tenant/products/create',
        ],
        [
            'label' => 'Purchase',
            'icon' => 'heroicon-o-clipboard-document',
            'url' => '/tenant/purchase-orders/create',
        ],
        [
            'label' => 'Sale',
            'icon' => 'heroicon-o-shopping-cart',
            'url' => '/tenant/sales-page',
        ],
        [
            'label' => 'Expense',
            'icon' => 'heroicon-o-banknotes',
            'url' => '/tenant/expenses',
        ],
        [
            'label' => 'Quotation',
            'icon' => 'heroicon-o-document-text',
            'url' => '/tenant/quotation-page',
        ],
        [
            'label' => 'Return',
            'icon' => 'heroicon-o-arrow-uturn-left',
            'url' => '/tenant/sales-return-page',
        ],
        [
            'label' => 'User',
            'icon' => 'heroicon-o-user',
            'url' => '/tenant/users/create',
        ],
        [
            'label' => 'Customer',
            'icon' => 'heroicon-o-users',
            'url' => '/tenant/customers/create',
        ],
        [
            'label' => 'Biller',
            'icon' => 'heroicon-o-document-currency-dollar',
            'url' => '#',
        ],
        [
            'label' => 'Supplier',
            'icon' => 'heroicon-o-truck',
            'url' => '/tenant/suppliers/create',
        ],
        [
            'label' => 'Transfer',
            'icon' => 'heroicon-o-arrow-left-right',
            'url' => '#',
        ],
    ];

    public function render()
    {
        return view('components.quick-create-modal');
    }
}

