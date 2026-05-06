<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressFabric extends Model
{
    use HasFactory;

    public const PURCHASE_PENDING_DRESS_STATUSES = [
        'confermato',
    ];

    protected $fillable = [
        'dress_id',
        'fabric_id',
        'supplier_id',
        'name',
        'type',
        'meters',
        'purchase_price',
        'client_price',
        'color_code',
        'supplier',
        'photo_path',
    ];

    protected $casts = [
        'meters' => 'float',
        'purchase_price' => 'decimal:2',
        'client_price' => 'decimal:2',
    ];

    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function supplierRecord(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function getProfitAttribute(): float
    {
        return (float) ($this->client_price ?? 0) - (float) ($this->purchase_price ?? 0);
    }

    public function getTotalPurchaseCostAttribute(): float
    {
        return (float) ($this->meters ?? 0) * (float) ($this->purchase_price ?? 0);
    }

    public function getTotalClientCostAttribute(): float
    {
        return (float) ($this->meters ?? 0) * (float) ($this->client_price ?? 0);
    }

    public function scopeForDressStatus(Builder $query, string $status): Builder
    {
        return $query->whereHas('dress', fn ($q) => $q->where('status', $status));
    }

    public function scopePendingPurchase(Builder $query): Builder
    {
        return $query->whereHas('dress', fn ($q) => $q->whereIn('status', self::PURCHASE_PENDING_DRESS_STATUSES));
    }

    public function scopeInLavorazione(Builder $query): Builder
    {
        return $query->forDressStatus('in_lavorazione');
    }

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

        static::saved(function (self $fabric) {
            $dress = $fabric->dress;
            if ($dress) {
                $dress->loadMissing('fabrics', 'extras');
                $dress->recalcFinancials(true);
            }
        });

        static::deleted(function (self $fabric) {
            $dress = $fabric->dress;
            if ($dress) {
                $dress->loadMissing('fabrics', 'extras');
                $dress->recalcFinancials(true);
            }
        });
    }
}
