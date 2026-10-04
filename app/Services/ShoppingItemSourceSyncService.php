<?php

namespace App\Services;

use App\Models\ShoppingItem;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class ShoppingItemSourceSyncService
{
    public function sync(Model $source): void
    {
        $supplier = $source->supplier_id
            ? Supplier::query()->find($source->supplier_id)
            : null;

        ShoppingItem::query()->updateOrCreate(
            [
                'source_type' => $source::class,
                'source_id' => $source->getKey(),
            ],
            [
                'supplier_id' => $source->supplier_id,
                'name' => $source->name ?: 'Articolo generico',
                'color_code' => $source->color_code,
                'price' => $source->price ?? 0,
                'quantity' => $source->quantity ?? 1,
                'unit_type' => $source->unit_type ?: 'pezzi',
                'supplier' => $supplier?->name,
                'photo_path' => $source->photo_path,
            ],
        );
    }

    public function delete(Model $source): void
    {
        ShoppingItem::query()
            ->where('source_type', $source::class)
            ->where('source_id', $source->getKey())
            ->delete();
    }
}
