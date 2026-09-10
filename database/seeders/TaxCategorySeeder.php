<?php

namespace Database\Seeders;

use App\Models\TaxCategory;
use Illuminate\Database\Seeder;

class TaxCategorySeeder extends Seeder
{
    public function run(): void
    {
        $taxCategories = [
            [
                'name' => 'Standard Rated',
                'code' => 'A',
                'rate' => 18.00,
                'description' => 'Standard VAT rate for most goods and services',
            ],
            [
                'name' => 'Zero Rated',
                'code' => 'B',
                'rate' => 0.00,
                'description' => 'Zero-rated supplies (e.g., exports)',
            ],
            [
                'name' => 'Exempt',
                'code' => 'C',
                'rate' => 0.00,
                'description' => 'VAT exempt supplies (e.g., financial services)',
            ],
            [
                'name' => 'Non VAT',
                'code' => 'D',
                'rate' => 0.00,
                'description' => 'Supplies outside the scope of VAT',
            ],
        ];

        foreach ($taxCategories as $category) {
            TaxCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
