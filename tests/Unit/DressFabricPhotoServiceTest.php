<?php

namespace Tests\Unit;

use App\Models\Fabric;
use App\Services\DressFabricPhotoService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DressFabricPhotoServiceTest extends TestCase
{
    public function test_copies_inventory_photo_into_dress_fabrics_directory(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('fabrics/mikado.jpg', 'fabric-photo');

        $fabric = new Fabric([
            'image' => 'fabrics/mikado.jpg',
        ]);

        $path = app(DressFabricPhotoService::class)->copyFromInventory($fabric);

        $this->assertNotNull($path);
        $this->assertStringStartsWith('dress-fabrics/', $path);
        $this->assertNotSame('fabrics/mikado.jpg', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('fabric-photo', Storage::disk('public')->get($path));
    }

    public function test_returns_null_when_inventory_photo_is_missing(): void
    {
        Storage::fake('public');

        $fabric = new Fabric([
            'image' => 'fabrics/missing.jpg',
        ]);

        $this->assertNull(
            app(DressFabricPhotoService::class)->copyFromInventory($fabric)
        );
    }
}
