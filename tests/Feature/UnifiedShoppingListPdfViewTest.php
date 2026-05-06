<?php

namespace Tests\Feature;

use App\Models\Dress;
use App\Models\DressFabric;
use App\Models\ShoppingItem;
use App\Services\UnifiedShoppingListPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedShoppingListPdfViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_unified_print_merges_manual_and_automatic_items_by_supplier(): void
    {
        ShoppingItem::query()->create([
            'name' => 'Candace',
            'color_code' => 'X2',
            'quantity' => 1.5,
            'unit_type' => 'metri',
            'supplier' => 'Casdit',
            'price' => 60,
        ]);

        ShoppingItem::query()->create([
            'name' => 'Gancini nude',
            'color_code' => null,
            'quantity' => 3,
            'unit_type' => 'pezzi',
            'supplier' => 'Lucci',
            'price' => 2.5,
        ]);

        ShoppingItem::query()->create([
            'name' => 'Gia comprato',
            'color_code' => null,
            'quantity' => 1,
            'unit_type' => 'pezzi',
            'supplier' => 'Casdit',
            'price' => 1,
            'purchase_date' => now(),
        ]);

        $dress = Dress::query()->create([
            'customer_name' => 'Maria Test',
            'phone_number' => '3331234567',
            'status' => 'confermato',
        ]);

        DressFabric::query()->create([
            'dress_id' => $dress->id,
            'name' => 'Candace',
            'type' => 'Tulle',
            'meters' => 2.5,
            'purchase_price' => 60,
            'color_code' => 'X2',
            'supplier' => 'Casdit',
        ]);

        DressFabric::query()->create([
            'dress_id' => $dress->id,
            'name' => 'Candace',
            'type' => 'Tulle',
            'meters' => 1.0,
            'purchase_price' => 60,
            'color_code' => 'X1',
            'supplier' => 'Casdit',
        ]);

        $payload = app(UnifiedShoppingListPdfService::class)->buildUnified();

        $html = view('pdf.shopping-list-unified', $payload)->render();

        $this->assertStringContainsString('Lista della Spesa', $html);
        $this->assertStringContainsString('Casdit', $html);
        $this->assertStringContainsString('Lucci', $html);
        $this->assertStringContainsString('Candace', $html);
        $this->assertStringContainsString('Gancini nude', $html);
        $this->assertStringContainsString('4,00', $html);
        $this->assertStringContainsString('3,00', $html);
        $this->assertStringContainsString('5,00 mt', $html);
        $this->assertStringContainsString('3,00 pz', $html);
        $this->assertStringNotContainsString('Gia comprato', $html);
        $this->assertStringContainsString('Generato automaticamente dal gestionale', $html);
    }
}
