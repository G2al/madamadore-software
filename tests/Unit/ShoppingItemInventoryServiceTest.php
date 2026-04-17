<?php

namespace Tests\Unit;

use App\Models\Fabric;
use App\Services\ShoppingItemInventoryService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShoppingItemInventoryServiceTest extends TestCase
{
    public function test_builds_shopping_item_payload_from_inventory_fabric(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('fabrics/mikado.jpg', 'fabric-photo');

        $fabric = new Fabric([
            'name' => 'Mikado',
            'color_code' => 'MK-101',
            'supplier' => 'Fornitore A',
            'purchase_price' => 18.5,
            'image' => 'fabrics/mikado.jpg',
        ]);

        $payload = app(ShoppingItemInventoryService::class)->payloadFor($fabric);

        $this->assertSame('Mikado', $payload['name']);
        $this->assertSame('MK-101', $payload['color_code']);
        $this->assertSame(18.5, $payload['price']);
        $this->assertSame('Fornitore A', $payload['supplier']);
        $this->assertSame('metri', $payload['unit_type']);
        $this->assertStringStartsWith('shopping-items/', $payload['photo_path']);
        Storage::disk('public')->assertExists($payload['photo_path']);
        $this->assertSame('fabric-photo', Storage::disk('public')->get($payload['photo_path']));
    }

    public function test_returns_empty_payload_when_inventory_fabric_is_missing(): void
    {
        $this->assertSame([], app(ShoppingItemInventoryService::class)->payloadFor(null));
    }
}
