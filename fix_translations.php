<?php

// ============================================================
// STEP 1: Add missing Arabic translations & new keys
// ============================================================
$arFile = __DIR__ . '/resources/lang/ar/navigation.php';
$enFile = __DIR__ . '/resources/lang/en/navigation.php';

$ar = include $arFile;
$en = include $enFile;

// Missing keys from en that don't exist in ar
$newAr = [
    'sales_report' => 'تقرير المبيعات',
    'purchase_report' => 'تقرير المشتريات',
    'profit_loss' => 'الربح والخسارة',
    'inventory_report' => 'تقرير المخزون',
    'customer_report' => 'تقرير العملاء',
    'supplier_report' => 'تقرير الموردين',
    'tax_report' => 'تقرير الضرائب',
    'expense_report' => 'تقرير المصروفات',
    'income_report' => 'تقرير الايرادات',
    'credit_aging' => 'تقادم الائتمان',
    'best_sellers' => 'الاكثر مبيعا',
    // Additional keys needed for pages that currently have hardcoded strings
    'payment_methods' => 'طرق الدفع',
    'discounts' => 'الخصومات',
    'tax_categories' => 'فئات الضرائب',
    'services' => 'الخدمات',
    'sold_stock' => 'المخزون المباع',
    'stock_history' => 'سجل المخزون',
    'product_report' => 'تقرير المنتجات',
    'product_expiry' => 'تاريخ انتهاء المنتجات',
    'product_quantity_alert' => 'تنبيه كمية المنتج',
    'invoice_report' => 'تقرير الفواتير',
    'supplier_due' => 'مستحقات الموردين',
    'customer_due' => 'مستحقات العملاء',
    'customer_ledger' => 'دفتر العملاء',
    'credit_balance' => 'رصيد الائتمان',
    'credit_outstanding' => 'الائتمان المستحق',
    'credit_payments' => 'مدفوعات الائتمان',
    'annual_report' => 'التقرير السنوي',
    'product_serials' => 'ارقام التسلسل',
    'products_serial_numbers' => 'منتجات بارقام التسلسل',
];

foreach ($newAr as $key => $val) {
    if (!isset($ar[$key])) {
        $ar[$key] = $val;
    }
}

// Write updated Arabic file
$arContent = "<?php\n\nreturn [\n";
foreach ($ar as $key => $value) {
    $escapedVal = str_replace("'", "\\'", $value);
    $arContent .= "    '{$key}' => '{$escapedVal}',\n";
}
$arContent .= "];\n";
file_put_contents($arFile, $arContent);
echo "Updated ar/navigation.php with " . count($newAr) . " new keys.\n";

// Also add the new keys to English file
$newEn = [
    'payment_methods' => 'Payment Methods',
    'discounts' => 'Discounts',
    'tax_categories' => 'Tax Categories',
    'services' => 'Services',
    'sold_stock' => 'Sold Stock',
    'stock_history' => 'Stock History',
    'product_report' => 'Product Report',
    'product_expiry' => 'Product Expiry',
    'product_quantity_alert' => 'Product Quantity Alert',
    'invoice_report' => 'Invoice Report',
    'supplier_due' => 'Supplier Due',
    'customer_due' => 'Customer Due',
    'customer_ledger' => 'Customer Ledger',
    'credit_balance' => 'Credit Balance',
    'credit_outstanding' => 'Credit Outstanding',
    'credit_payments' => 'Credit Payments',
    'annual_report' => 'Annual Report',
    'product_serials' => 'Product Serials',
    'products_serial_numbers' => 'Products with Serial Numbers',
];

foreach ($newEn as $key => $val) {
    if (!isset($en[$key])) {
        $en[$key] = $val;
    }
}

$enContent = "<?php\n\nreturn [\n";
foreach ($en as $key => $value) {
    $escapedVal = str_replace("'", "\\'", $value);
    $enContent .= "    '{$key}' => '{$escapedVal}',\n";
}
$enContent .= "];\n";
file_put_contents($enFile, $enContent);
echo "Updated en/navigation.php with " . count($newEn) . " new keys.\n";

// ============================================================
// STEP 2: Map pages to translation keys for getNavigationLabel
// ============================================================
$labelMappings = [
    'SalesReportPage.php' => 'navigation.sales_report',
    'PurchaseReportPage.php' => 'navigation.purchase_report',
    'ProfitAndLossPage.php' => 'navigation.profit_loss',
    'InventoryReportPage.php' => 'navigation.inventory_report',
    'CustomerReportPage.php' => 'navigation.customer_report',
    'SupplierReportPage.php' => 'navigation.supplier_report',
    'TaxReportPage.php' => 'navigation.tax_report',
    'ExpenseReportPage.php' => 'navigation.expense_report',
    'IncomeReportPage.php' => 'navigation.income_report',
    'CreditAgingReportPage.php' => 'navigation.credit_aging',
    'BestSellerPage.php' => 'navigation.best_sellers',
    'SoldStockPage.php' => 'navigation.sold_stock',
    'StockHistoryPage.php' => 'navigation.stock_history',
    'ProductReportPage.php' => 'navigation.product_report',
    'ProductExpiryReportPage.php' => 'navigation.product_expiry',
    'ProductQuantityAlertPage.php' => 'navigation.product_quantity_alert',
    'InvoiceReportPage.php' => 'navigation.invoice_report',
    'SupplierDueReportPage.php' => 'navigation.supplier_due',
    'CustomerDueReportPage.php' => 'navigation.customer_due',
    'CustomerLedger.php' => 'navigation.customer_ledger',
    'CreditBalanceReportPage.php' => 'navigation.credit_balance',
    'CreditOutstandingReportPage.php' => 'navigation.credit_outstanding',
    'CreditPaymentsReportPage.php' => 'navigation.credit_payments',
    'AnnualReportPage.php' => 'navigation.annual_report',
    'InvoicePage.php' => 'navigation.invoices',
    'InvoiceDetailsPage.php' => 'navigation.invoices',
    'ManageTaxAndEfris.php' => 'navigation.tax_efris',
    'ProductSerialsPage.php' => 'navigation.product_serials',
    'ProductsSerialsPage.php' => 'navigation.products_serial_numbers',
    'ProductVariantsPage.php' => 'navigation.product_variants',
    'PrintBarcodesPage.php' => 'navigation.print_barcode',
    'PrintQrCodesPage.php' => 'navigation.print_qr_code',
    'QuotationPage.php' => 'navigation.quotations',
    'SalesPage.php' => 'navigation.sales_history',
    'SalesReturnPage.php' => 'navigation.sales_return',
    'SalesTerminal.php' => 'navigation.pos',
    'StockDashboard.php' => 'navigation.stock_dashboard',
    'UnitsPage.php' => 'navigation.units',
    'VariantAttributesPage.php' => 'navigation.variant_attributes',
    'ExchangeRatesPage.php' => 'navigation.exchange_rates',
    'LocalizationSettingsPage.php' => 'navigation.localization',
    'EmailPage.php' => 'navigation.email',
    'EmailIntegrationPage.php' => 'navigation.email_settings',
    'BusinessManagementPage.php' => 'navigation.business_management',
    'AccountManagementPage.php' => 'navigation.account_management',
    'UserPreferencesPage.php' => 'navigation.my_preferences',
    'AdminDashboard.php' => 'navigation.admin_dashboard',
    'AdminDashboard2Page.php' => 'navigation.admin_dashboard_2',
    'SalesDashboardPage.php' => 'navigation.sales_dashboard',
];

// Navigation group mappings for pages that have hardcoded groups
$groupMappings = [
    'SalesReportPage.php' => 'navigation.reports',
    'PurchaseReportPage.php' => 'navigation.reports',
    'ProfitAndLossPage.php' => 'navigation.reports',
    'InventoryReportPage.php' => 'navigation.reports',
    'CustomerReportPage.php' => 'navigation.reports',
    'SupplierReportPage.php' => 'navigation.reports',
    'TaxReportPage.php' => 'navigation.reports',
    'ExpenseReportPage.php' => 'navigation.reports',
    'IncomeReportPage.php' => 'navigation.reports',
    'CreditAgingReportPage.php' => 'navigation.reports',
    'BestSellerPage.php' => 'navigation.reports',
    'SoldStockPage.php' => 'navigation.reports',
    'StockHistoryPage.php' => 'navigation.reports',
    'ProductReportPage.php' => 'navigation.reports',
    'ProductExpiryReportPage.php' => 'navigation.reports',
    'ProductQuantityAlertPage.php' => 'navigation.reports',
    'InvoiceReportPage.php' => 'navigation.reports',
    'SupplierDueReportPage.php' => 'navigation.reports',
    'CustomerDueReportPage.php' => 'navigation.reports',
    'CustomerLedger.php' => 'navigation.reports',
    'CreditBalanceReportPage.php' => 'navigation.reports',
    'CreditOutstandingReportPage.php' => 'navigation.reports',
    'CreditPaymentsReportPage.php' => 'navigation.reports',
    'AnnualReportPage.php' => 'navigation.reports',
    'InvoicePage.php' => 'navigation.sales',
    'InvoiceDetailsPage.php' => 'navigation.sales',
    'ManageTaxAndEfris.php' => 'navigation.compliance',
];

// Resource label/group mappings (for files without getNavigationLabel)
$resourceMappings = [
    'PaymentMethodResource.php' => ['label' => 'navigation.payment_methods', 'group' => null],
    'DiscountResource.php' => ['label' => 'navigation.discounts', 'group' => null],
    'TaxCategoryResource.php' => ['label' => 'navigation.tax_categories', 'group' => null],
    'ServiceResource.php' => ['label' => 'navigation.services', 'group' => null],
];

$dir = __DIR__ . '/app/Filament/Tenant';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$fixedCount = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    
    $basename = $file->getBasename();
    $content = file_get_contents($file->getPathname());
    $original = $content;
    
    // Fix getNavigationLabel with hardcoded strings
    if (isset($labelMappings[$basename])) {
        $transKey = $labelMappings[$basename];
        // Replace hardcoded return in getNavigationLabel
        $content = preg_replace(
            '/(function getNavigationLabel\(\):\s*string\s*\{[^}]*return\s+)[\'"][^"\']*[\'"](\s*;)/s',
            '$1__(\'' . $transKey . '\')$2',
            $content
        );
    }
    
    // Fix getNavigationGroup with hardcoded strings
    if (isset($groupMappings[$basename])) {
        $transKey = $groupMappings[$basename];
        $content = preg_replace(
            '/(function getNavigationGroup\(\):\s*\?string\s*\{[^}]*return\s+)[\'"][^"\']*[\'"](\s*;)/s',
            '$1__(\'' . $transKey . '\')$2',
            $content
        );
    }
    
    // Fix getTitle with hardcoded strings — replace with __() version using the label key
    if (isset($labelMappings[$basename])) {
        $transKey = $labelMappings[$basename];
        $content = preg_replace(
            '/(function getTitle\(\):\s*string\s*\{[^}]*return\s+)[\'"][^"\']*[\'"](\s*;)/s',
            '$1__(\'' . $transKey . '\')$2',
            $content
        );
    }

    // Fix Resources that need getNavigationLabel added
    if (isset($resourceMappings[$basename])) {
        $mapping = $resourceMappings[$basename];
        $labelKey = $mapping['label'];
        
        // Check if it doesn't already have getNavigationLabel
        if (!preg_match('/function getNavigationLabel/', $content)) {
            // Add getNavigationLabel method before the first public static function or form method
            $content = preg_replace(
                '/(class \w+ extends Resource\s*\{[^}]*?)(public static function (?:form|table))/s',
                '$1' . "    public static function getNavigationLabel(): string\n    {\n        return __('" . $labelKey . "');\n    }\n\n    " . '$2',
                $content,
                1
            );
        }
    }
    
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        echo "Fixed: {$file->getPathname()}\n";
        $fixedCount++;
    }
}

echo "\nFixed {$fixedCount} files.\n";
echo "Done!\n";
