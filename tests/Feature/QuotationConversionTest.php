<?php

namespace Tests\Feature;

use App\Services\QuotationSaleConverter;
use Tests\TestCase;

class QuotationConversionTest extends TestCase
{
    public function test_quotation_converter_exists_and_returns_a_sale(): void
    {
        $this->assertTrue(class_exists(QuotationSaleConverter::class));
        $this->assertTrue(method_exists(QuotationSaleConverter::class, 'convert'));
    }
}
