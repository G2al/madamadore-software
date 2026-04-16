<?php

namespace App\Services;

use App\Models\Fabric;

class ShoppingItemInventoryService
{
    public function __construct(
        private readonly DressFabricPhotoService $photoService,
    ) {
    }

    public function payloadFor(?Fabric $fabric): array
    {
        if (! $fabric) {
            return [];
        }

        return [
            'name' => $fabric->name,
            'price' => $fabric->purchase_price !== null ? (float) $fabric->purchase_price : null,
            'supplier' => $fabric->supplier,
            'unit_type' => 'metri',
            'photo_path' => $this->photoService->copyToDirectory($fabric, 'shopping-items'),
        ];
    }
}
