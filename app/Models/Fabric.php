<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fabric extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'color_code',
        'supplier_id',
        'supplier',
        'purchase_price',
        'client_price',
        'image',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'client_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $fabric): void {
            if (! $fabric->supplier_id) {
                return;
            }

            $supplier = $fabric->relationLoaded('supplierRecord')
                ? $fabric->supplierRecord
                : Supplier::query()->find($fabric->supplier_id);

            if ($supplier) {
                $fabric->supplier = $supplier->name;
            }
        });
    }

    public function patterns()
    {
        return $this->hasMany(FabricPattern::class);
    }

    public function supplierRecord(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
