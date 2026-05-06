<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fabric_id',
        'supplier_id',
        'name',
        'color_code',
        'price',
        'quantity',
        'unit_type',
        'supplier',
        'photo_path',
        'purchase_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'purchase_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $shoppingItem): void {
            if (! $shoppingItem->supplier_id) {
                return;
            }

            $supplier = $shoppingItem->relationLoaded('supplierRecord')
                ? $shoppingItem->supplierRecord
                : Supplier::query()->find($shoppingItem->supplier_id);

            if ($supplier) {
                $shoppingItem->supplier = $supplier->name;
            }
        });
    }

    public function isPaid(): bool
    {
        return !is_null($this->purchase_date);
    }

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function supplierRecord(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
