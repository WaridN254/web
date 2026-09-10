<?php

namespace Tests\Feature;

use App\Models\Quotation;
use Tests\TestCase;

class QuotationPageTest extends TestCase
{
    public function test_quotation_model_exists_and_is_queryable(): void
    {
        $this->assertTrue(class_exists(Quotation::class));
        $this->assertNotNull(Quotation::query());
    }
}
