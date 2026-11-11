<?php

namespace App\Models;

use App\Services\ShoppingItemSourceSyncService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'name',
        'photo_path',
        'price',
        'color_code',
        'quantity',
        'unit_type',
        'supplier_id',
        'is_to_purchase',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_to_purchase' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $expense): void {
            app(ShoppingItemSourceSyncService::class)->sync($expense);
        });
        static::deleted(fn (self $expense) => app(ShoppingItemSourceSyncService::class)->delete($expense));
    }

    public function dress()
    {
        return $this->belongsTo(Dress::class);
    }

    public function supplierRecord(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/'.$this->photo_path)
            : null;
    }
}
