<?php

namespace Tests\Unit;

use App\Models\DressFabric;
use PHPUnit\Framework\TestCase;

class DressFabricTest extends TestCase
{
    public function test_handles_nulls_without_errors(): void
    {
        $f = new DressFabric([
            'meters' => null,
            'purchase_price' => null,
            'client_price' => null,
        ]);

        $this->assertIsFloat($f->total_purchase_cost);
        $this->assertSame(0.0, $f->total_purchase_cost);
        $this->assertSame(0.0, $f->total_client_cost);
        $this->assertIsFloat($f->profit);
    }

    public function test_totals_are_correct(): void
    {
        $f = new DressFabric([
            'meters' => 12.5,
            'purchase_price' => 4.20,
            'client_price' => 6.00,
        ]);

        $this->assertSame(52.5, round($f->total_purchase_cost, 2));
        $this->assertSame(75.0, round($f->total_client_cost, 2));
    }
}
