<?php

// Map of basename => translation key for getNavigationLabel
$labelMap = [
    // Resources without getNavigationLabel
    'PaymentMethodResource.php' => 'navigation.payment_methods',
    'DiscountResource.php' => 'navigation.discounts',
    'TaxCategoryResource.php' => 'navigation.tax_categories',
    'ServiceResource.php' => 'navigation.services',
];

$dir = __DIR__ . '/app/Filament/Tenant';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$fixed = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    
    $basename = $file->getBasename();
    $content = file_get_contents($file->getPathname());
    $original = $content;
    
    // Add getNavigationLabel to resources that don't have it
    if (isset($labelMap[$basename]) && !preg_match('/function getNavigationLabel/', $content)) {
        $key = $labelMap[$basename];
        $method = "\n    public static function getNavigationLabel(): string\n    {\n        return __('{$key}');\n    }\n";
        
        // Insert before the first 'public static function form' or 'public static function table'
        $content = preg_replace(
            '/(    public static function (?:form|table)\()/',
            $method . "\n" . '$1',
            $content,
            1
        );
    }
    
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        echo "Fixed: {$file->getPathname()}\n";
        $fixed++;
    }
}

echo "\nFixed {$fixed} resource files.\n";
echo "Done!\n";
