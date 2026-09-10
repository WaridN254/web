<?php

namespace App\Filament\Tenant;

class PermissionMap
{
    private static array $map = [
        // Resources
        'ProductResource' => 'can_manage_products',
        'CategoryResource' => 'can_manage_products',
        'ProductBrandResource' => 'can_manage_products',
        'UnitMeasureResource' => 'can_manage_products',
        'VariantAttributeResource' => 'can_manage_variants',
        'ServiceResource' => 'can_manage_services',
        'StockMovementResource' => 'can_manage_stock',
        'StockAuditResource' => 'can_manage_stock',
        'SupplierResource' => 'can_manage_stock',
        'PurchaseOrderResource' => 'can_manage_stock',
        'WarrantyResource' => 'can_manage_warranties',
        'DiscountResource' => 'can_manage_discounts',
        'PaymentMethodResource' => 'can_manage_payment_methods',
        'TaxCategoryResource' => 'can_manage_tax_efris',
        'DocumentVaultResource' => 'can_manage_document_vault',
        'ComplianceReminderResource' => 'can_manage_compliance',
        'CustomerResource' => 'can_manage_customers',
        'UserResource' => 'can_manage_users',
        'RoleResource' => 'can_manage_users',
        'BranchResource' => 'can_manage_branches',
        'StockTransferResource' => 'can_manage_stock_transfers',

        // Pages
        'AdminDashboard' => 'can_access_admin_dashboard',
        'AdminDashboard2Page' => 'can_access_dashboard_2',
        'SalesDashboardPage' => 'can_access_sales_dashboard',
        'InvoicePage' => 'can_view_sales_history',
        'InvoiceDetailsPage' => 'can_view_sales_history',
        'SalesReturnPage' => 'can_process_refunds',
        'QuotationPage' => 'can_manage_quotations',
        'CustomerLedger' => 'can_manage_customers',
        'UnitsPage' => 'can_manage_products',
        'PrintBarcodesPage' => 'can_manage_products',
        'PrintQrCodesPage' => 'can_manage_products',
        'ProductSerialsPage' => 'can_manage_products',
        'ProductsSerialsPage' => 'can_manage_products',
        'VariantAttributesPage' => 'can_manage_variants',
        'ProductVariantsPage' => 'can_manage_variants',
        'StockDashboard' => 'can_manage_stock',
        'StockHistoryPage' => 'can_view_reports',
        'ManageTaxAndEfris' => 'can_manage_tax_efris',
        'BusinessManagementPage' => 'can_edit_settings',
        'SalesReportPage' => 'can_view_reports',
        'PurchaseReportPage' => 'can_view_reports',
        'ProfitAndLossPage' => 'can_view_reports',
        'ProductReportPage' => 'can_view_reports',
        'ProductQuantityAlertPage' => 'can_view_reports',
        'ProductExpiryReportPage' => 'can_view_reports',
        'InvoiceReportPage' => 'can_view_reports',
        'InventoryReportPage' => 'can_view_reports',
        'IncomeReportPage' => 'can_view_reports',
        'ExpenseReportPage' => 'can_view_reports',
        'CustomerReportPage' => 'can_view_reports',
        'CustomerDueReportPage' => 'can_view_reports',
        'CreditOutstandingReportPage' => 'can_view_reports',
        'CreditBalanceReportPage' => 'can_view_reports',
        'CreditPaymentsReportPage' => 'can_view_reports',
        'CreditAgingReportPage' => 'can_view_reports',
        'BestSellerPage' => 'can_view_reports',
        'AnnualReportPage' => 'can_view_reports',
        'SupplierReportPage' => 'can_view_reports',
        'SupplierDueReportPage' => 'can_view_reports',
        'TaxReportPage' => 'can_view_reports',
        'SoldStockPage' => 'can_view_reports',

        // Communication
        'EmailPage' => 'can_view_email',
        'EmailIntegrationPage' => 'can_manage_email_accounts',

        // Localization
        'LocalizationSettingsPage' => 'can_manage_localization_settings',
        'ExchangeRatesPage' => 'can_manage_exchange_rates',
        'UserPreferencesPage' => null,
    ];

    public static function for(string $class): ?string
    {
        return self::$map[class_basename($class)] ?? null;
    }
}
