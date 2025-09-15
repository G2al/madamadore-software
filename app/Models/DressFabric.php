<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DressFabric extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'name',
        'type',
        'meters',
        'purchase_price',
        'client_price',
        'color_code',
        'supplier',
    ];

    protected $casts = [
        'meters' => 'float',
        'purchase_price' => 'decimal:2',
        'client_price' => 'decimal:2',
    ];

    // Relationships
    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }

    // --- Accessors utili (per singolo tessuto) ---
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

    // --- Scopes ---
    /** Filtra i tessuti appartenenti ad abiti con un certo stato */
    public function scopeForDressStatus(Builder $query, string $status): Builder
    {
        return $query->whereHas('dress', fn ($q) => $q->where('status', $status));
    }

    /** Comodo per il caso più usato: solo abiti "in_lavorazione" */
    public function scopeInLavorazione(Builder $query): Builder
    {
        return $query->forDressStatus('in_lavorazione');
    }

    // --- Hooks: ricalcolo automatico dei totali dell'abito ---
    protected static function booted(): void
    {
        // create & update
        static::saved(function (self $fabric) {
            $dress = $fabric->dress;
            if ($dress) {
                $dress->loadMissing('fabrics', 'extras');
                $dress->recalcFinancials(true); // persiste total_purchase_cost, total_client_price, total_profit, remaining
            }
        });

        // delete
        static::deleted(function (self $fabric) {
            $dress = $fabric->dress;
            if ($dress) {
                $dress->loadMissing('fabrics', 'extras');
                $dress->recalcFinancials(true);
            }
        });
    }
}
