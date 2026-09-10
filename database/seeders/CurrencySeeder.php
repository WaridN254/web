<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    protected array $currencies = [
        // Major global currencies
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => "\u{20AC}", 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousands_separator' => '.', 'symbol_first' => 'after'],
        ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => "\u{00A3}", 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => "\u{00A5}", 'minor_unit' => 0, 'decimal_places' => 0, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => "\u{00A5}", 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => "\u{20B9}", 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],

        // African currencies
        ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'UGX', 'minor_unit' => 0, 'decimal_places' => 0, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before', 'is_default' => true],
        ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ' ', 'symbol_first' => 'before'],
        ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => "\u{20A6}", 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH\u{00A3}', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'RF', 'minor_unit' => 0, 'decimal_places' => 0, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'ETB', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'XOF', 'name' => 'CFA Franc', 'symbol' => 'CFA', 'minor_unit' => 0, 'decimal_places' => 0, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],

        // Middle East
        ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'AED', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'SAR', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],

        // Other
        ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'CA$', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousands_separator' => ',', 'symbol_first' => 'before'],
        ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => "'", 'thousands_separator' => '.', 'symbol_first' => 'before'],
        ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kc', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousands_separator' => ' ', 'symbol_first' => 'after'],
        ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousands_separator' => ' ', 'symbol_first' => 'after'],
        ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousands_separator' => ' ', 'symbol_first' => 'after'],
        ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zl', 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousands_separator' => ' ', 'symbol_first' => 'after'],
        ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => "\u{20BA}", 'minor_unit' => 2, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousands_separator' => '.', 'symbol_first' => 'after'],
    ];

    public function run(): void
    {
        foreach ($this->currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'minor_unit' => $currency['minor_unit'],
                    'decimal_places' => $currency['decimal_places'],
                    'decimal_separator' => $currency['decimal_separator'],
                    'thousands_separator' => $currency['thousands_separator'],
                    'symbol_first' => $currency['symbol_first'],
                    'is_active' => true,
                    'is_default' => $currency['is_default'] ?? false,
                ]
            );
        }
    }
}
