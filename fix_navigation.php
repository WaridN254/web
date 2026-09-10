<?php

$pagesDir = __DIR__ . '/app/Filament/Tenant/Pages';

$files = [
    'AccountManagementPage.php' => ['navigationLabel' => 'navigation.account_management', 'navigationGroup' => 'navigation.settings'],
    'BestSellerPage.php' => ['navigationLabel' => 'navigation.best_sellers', 'navigationGroup' => 'navigation.reports'],
    'AnnualReportPage.php' => ['navigationLabel' => 'navigation.reports', 'navigationGroup' => 'navigation.reports'],
    'CreditAgingReportPage.php' => ['navigationLabel' => 'navigation.credit_aging', 'navigationGroup' => 'navigation.reports'],
    'BusinessManagementPage.php' => ['navigationLabel' => 'navigation.business_management', 'navigationGroup' => 'navigation.settings'],
    'CreditPaymentsReportPage.php' => ['navigationLabel' => 'navigation.reports', 'navigationGroup' => 'navigation.reports'],
    'CreditOutstandingReportPage.php' => ['navigationLabel' => 'navigation.reports', 'navigationGroup' => 'navigation.reports'],
    'CreditBalanceReportPage.php' => ['navigationLabel' => 'navigation.reports', 'navigationGroup' => 'navigation.reports'],
    'EmailIntegrationPage.php' => ['navigationLabel' => 'navigation.email_settings', 'navigationGroup' => 'navigation.communication'],
    'CustomerLedger.php' => ['navigationLabel' => 'navigation.customers', 'navigationGroup' => 'navigation.customers'],
    'CustomerDueReportPage.php' => ['navigationLabel' => 'navigation.customer_report', 'navigationGroup' => 'navigation.reports'],
    'InvoiceReportPage.php' => ['navigationLabel' => 'navigation.invoices', 'navigationGroup' => 'navigation.reports'],
    'CustomerReportPage.php' => ['navigationLabel' => 'navigation.customer_report', 'navigationGroup' => 'navigation.reports'],
    'InvoicePage.php' => ['navigationLabel' => 'navigation.invoices', 'navigationGroup' => 'navigation.sales'],
    'InvoiceDetailsPage.php' => ['navigationLabel' => 'navigation.invoices', 'navigationGroup' => 'navigation.sales'],
    'InventoryReportPage.php' => ['navigationLabel' => 'navigation.inventory_report', 'navigationGroup' => 'navigation.reports'],
    'IncomeReportPage.php' => ['navigationLabel' => 'navigation.income_report', 'navigationGroup' => 'navigation.reports'],
    'ExpenseReportPage.php' => ['navigationLabel' => 'navigation.expense_report', 'navigationGroup' => 'navigation.reports'],
    'ExchangeRatesPage.php' => ['navigationLabel' => 'navigation.exchange_rates', 'navigationGroup' => 'navigation.settings'],
    'EmailPage.php' => ['navigationLabel' => 'navigation.email', 'navigationGroup' => 'navigation.communication'],
    'LocalizationSettingsPage.php' => ['navigationLabel' => 'navigation.localization', 'navigationGroup' => 'navigation.settings'],
    'ManageTaxAndEfris.php' => ['navigationLabel' => 'navigation.tax_efris', 'navigationGroup' => 'navigation.compliance'],
    'ProductsSerialsPage.php' => ['navigationLabel' => 'navigation.serials', 'navigationGroup' => 'navigation.inventory'],
    'ProductSerialsPage.php' => ['navigationLabel' => 'navigation.serials', 'navigationGroup' => 'navigation.inventory'],
    'ProductReportPage.php' => ['navigationLabel' => 'navigation.inventory_report', 'navigationGroup' => 'navigation.reports'],
    'ProductQuantityAlertPage.php' => ['navigationLabel' => 'navigation.inventory_report', 'navigationGroup' => 'navigation.reports'],
    'ProductExpiryReportPage.php' => ['navigationLabel' => 'navigation.inventory_report', 'navigationGroup' => 'navigation.reports'],
    'PurchaseReportPage.php' => ['navigationLabel' => 'navigation.purchase_report', 'navigationGroup' => 'navigation.reports'],
    'ProfitAndLossPage.php' => ['navigationLabel' => 'navigation.profit_loss', 'navigationGroup' => 'navigation.reports'],
    'ProductVariantsPage.php' => ['navigationLabel' => 'navigation.product_variants', 'navigationGroup' => 'navigation.inventory'],
    'QuotationPage.php' => ['navigationLabel' => 'navigation.quotations', 'navigationGroup' => 'navigation.sales'],
    'SalesReturnPage.php' => ['navigationLabel' => 'navigation.sales_return', 'navigationGroup' => 'navigation.sales'],
    'PrintBarcodesPage.php' => ['navigationLabel' => 'navigation.print_barcode', 'navigationGroup' => 'navigation.inventory'],
    'SalesReportPage.php' => ['navigationLabel' => 'navigation.sales_report', 'navigationGroup' => 'navigation.reports'],
    'SalesPage.php' => ['navigationLabel' => 'navigation.sales_history', 'navigationGroup' => 'navigation.sales'],
    'SalesTerminal.php' => ['navigationLabel' => 'navigation.pos', 'navigationGroup' => 'navigation.sales'],
    'SoldStockPage.php' => ['navigationLabel' => 'navigation.inventory_report', 'navigationGroup' => 'navigation.reports'],
    'VariantAttributesPage.php' => ['navigationLabel' => 'navigation.variant_attributes', 'navigationGroup' => 'navigation.inventory'],
    'UserPreferencesPage.php' => ['navigationLabel' => 'navigation.my_preferences', 'navigationGroup' => 'navigation.settings'],
    'UnitsPage.php' => ['navigationLabel' => 'navigation.units', 'navigationGroup' => 'navigation.inventory'],
    'TaxReportPage.php' => ['navigationLabel' => 'navigation.tax_report', 'navigationGroup' => 'navigation.reports'],
    'SupplierReportPage.php' => ['navigationLabel' => 'navigation.supplier_report', 'navigationGroup' => 'navigation.reports'],
    'SupplierDueReportPage.php' => ['navigationLabel' => 'navigation.supplier_report', 'navigationGroup' => 'navigation.reports'],
    'StockHistoryPage.php' => ['navigationLabel' => 'navigation.stock_ledger', 'navigationGroup' => 'navigation.reports'],
    'StockDashboard.php' => ['navigationLabel' => 'navigation.stock_dashboard', 'navigationGroup' => 'navigation.stock'],
    'PrintQrCodesPage.php' => ['navigationLabel' => 'navigation.print_qr_code', 'navigationGroup' => 'navigation.inventory'],
    'AdminDashboard.php' => ['navigationLabel' => 'navigation.admin_dashboard'],
    'AdminDashboard2Page.php' => ['navigationLabel' => 'navigation.admin_dashboard_2'],
    'SalesDashboardPage.php' => ['navigationLabel' => 'navigation.sales_dashboard'],
];

$fixed = 0;

foreach ($files as $file => $props) {
    $path = $pagesDir . '/' . $file;
    if (!file_exists($path)) {
        echo "SKIP: $file (not found)\n";
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    // Remove navigationLabel static property line
    if (isset($props['navigationLabel'])) {
        $escapedKey = preg_quote($props['navigationLabel'], '/');
        // Match the line with various whitespace/indentation patterns
        $content = preg_replace('/^\s*protected static \?string \$navigationLabel = __\([\x27\x22]' . $escapedKey . '[\x27\x22]\);\s*$/m', '', $content);
    }

    // Remove navigationGroup static property line
    if (isset($props['navigationGroup'])) {
        $escapedKey = preg_quote($props['navigationGroup'], '/');
        $content = preg_replace('/^\s*protected static \\\\UnitEnum\|string\|null \$navigationGroup = __\([\x27\x22]' . $escapedKey . '[\x27\x22]\);\s*$/m', '', $content);
    }

    // Clean up multiple blank lines left behind (max 2 consecutive)
    $content = preg_replace('/\n{3,}/', "\n\n", $content);

    // Check for existing methods
    $hasNavLabelMethod = strpos($content, 'function getNavigationLabel()') !== false;
    $hasNavGroupMethod = strpos($content, 'function getNavigationGroup()') !== false;

    // If methods already exist, update return values
    if ($hasNavLabelMethod && isset($props['navigationLabel'])) {
        $content = preg_replace(
            '/(function getNavigationLabel\(\)[^}]*return\s+)[^;]+;/',
            '$1' . "__('{$props['navigationLabel']}');",
            $content
        );
    }
    if ($hasNavGroupMethod && isset($props['navigationGroup'])) {
        $content = preg_replace(
            '/(function getNavigationGroup\(\)[^}]*return\s+)[^;]+;/',
            '$1' . "__('{$props['navigationGroup']}');",
            $content
        );
    }

    // Build methods to insert (only for non-existing methods)
    $methods = '';
    if (isset($props['navigationLabel']) && !$hasNavLabelMethod) {
        $methods .= "\n    public static function getNavigationLabel(): ?string\n    {\n        return __('{$props['navigationLabel']}');\n    }\n";
    }
    if (isset($props['navigationGroup']) && !$hasNavGroupMethod) {
        $methods .= "\n    public static function getNavigationGroup(): string|false|null\n    {\n        return __('{$props['navigationGroup']}');\n    }\n";
    }

    if ($methods !== '') {
        // Find the class closing brace by counting braces
        $lines = explode("\n", $content);
        $braceCount = 0;
        $classEndLine = -1;
        $inClass = false;

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            // Count braces (ignoring strings for simplicity - good enough for these files)
            foreach (str_split($line) as $ch) {
                if ($ch === '{') {
                    $braceCount++;
                    $inClass = true;
                } elseif ($ch === '}') {
                    $braceCount--;
                    if ($inClass && $braceCount === 0) {
                        $classEndLine = $i;
                        break 2;
                    }
                }
            }
        }

        if ($classEndLine >= 0) {
            // Insert methods before the class closing brace
            array_splice($lines, $classEndLine, 0, explode("\n", rtrim($methods)));
            $content = implode("\n", $lines);
        }
    }

    // Clean up trailing whitespace on lines
    $content = preg_replace('/[ \t]+$/m', '', $content);
    // Ensure exactly one newline at end
    $content = rtrim($content) . "\n";

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "FIXED: $file\n";
        $fixed++;
    } else {
        echo "NO CHANGE: $file\n";
    }
}

echo "\nDone: $fixed files fixed\n";
