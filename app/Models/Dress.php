<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'phone_number',
        'ceremony_date',
        'ceremony_type',
        'ceremony_holder',
        'delivery_date',
        'sketch_image',
        'final_image',
        'notes',
        'estimated_time',
        'manufacturing_price',
        'deposit',
        'remaining',
        'status',
        'fabric_bought_at',
        'cut_completed_at',
        'final_measurements_notes',
        'manual_client_price',
        'pronta_misura_notes',
        'use_manual_price',
        'archived_at', // 👈 nuovo campo fillable
    ];

    protected $casts = [
        'ceremony_date' => 'date',
        'delivery_date' => 'date',
        'deposit' => 'decimal:2',
        'manufacturing_price' => 'decimal:2',
        'remaining' => 'decimal:2',
        'total_purchase_cost' => 'decimal:2',
        'total_client_price' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'manual_client_price' => 'decimal:2',
        'use_manual_price' => 'boolean',
        'archived_at' => 'datetime', // 👈 cast per archivio
    ];

    // 🔹 RELAZIONI
    public function fabrics(): HasMany
    {
        return $this->hasMany(DressFabric::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(DressExtra::class);
    }

    public function measurements(): HasOne
    {
        return $this->hasOne(DressMeasurement::class);
    }

    public function customMeasurements(): HasMany
    {
        return $this->hasMany(DressCustomMeasurement::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(DressExpense::class);
    }

    // 🔹 CALCOLI TOTALI
    public function getTotalPurchaseCostAttribute(): float
    {
        return $this->fabrics->sum(fn($f) => (float) $f->meters * (float) $f->purchase_price);
    }

    public function getTotalClientPriceAttribute(): float
    {
        $calculated =
            $this->fabrics->sum(fn($f) => (float) $f->meters * (float) $f->client_price)
            + $this->extras->sum('cost')
            + (float) ($this->manufacturing_price ?? 0);

        if ($this->use_manual_price && $this->manual_client_price !== null) {
            return (float) $this->manual_client_price;
        }

        return (float) $calculated;
    }

    public function getProfitAttribute(): float
    {
        return $this->total_client_price - $this->total_purchase_cost;
    }

    public function getCustomerInfoAttribute(): string
    {
        return "<strong>{$this->customer_name}</strong><br>
                <small class='text-gray-500'>{$this->phone_number}</small>";
    }

    public function getCeremonyInfoAttribute(): string
    {
        $date = $this->ceremony_date?->format('d/m/Y') ?? '-';
        $type = $this->ceremony_type ?? '-';

        return "<strong>{$date}</strong><br>
                <small class='text-gray-500'>{$type}</small>";
    }

    // 🔹 RICALCOLO VALORI
    public function recalcFinancials(bool $persist = false): array
    {
        $fabrics = $this->fabrics->map(fn($f) => [
            'meters' => (float) $f->meters,
            'purchase_price' => (float) $f->purchase_price,
            'client_price' => (float) $f->client_price,
        ])->all();

        $extras = $this->extras->map(fn($e) => [
            'cost' => (float) $e->cost,
        ])->all();

        $calc = \App\Services\DressCalculator::calculate(
            $fabrics,
            $extras,
            (float) ($this->deposit ?? 0),
            (float) ($this->manufacturing_price ?? 0),
        );

        if ($this->use_manual_price && $this->manual_client_price !== null) {
            $calc['total_client_price'] = (float) $this->manual_client_price;
            $calc['total_profit'] = $calc['total_client_price'] - $calc['total_purchase_cost'];
            $calc['remaining'] = $calc['total_client_price'] - (float) ($this->deposit ?? 0);
        }

        if ($persist) {
            $this->forceFill([
                'total_purchase_cost' => $calc['total_purchase_cost'],
                'total_client_price'  => $calc['total_client_price'],
                'total_profit'        => $calc['total_profit'],
                'remaining'           => $calc['remaining'],
            ])->saveQuietly();
        }

        return $calc;
    }

    // 🔹 EVENTI E GLOBAL SCOPE
    protected static function booted(): void
    {
        parent::booted();

        // Escludi tutti gli abiti archiviati dalle query standard
        static::addGlobalScope('notArchived', function ($query) {
            $query->whereNull('archived_at');
        });

        // Ricalcolo automatico e cache invalidation
        static::saved(function (self $dress) {
            $dress->loadMissing('fabrics', 'extras');
            $dress->recalcFinancials(true);

            if ($dress->delivery_date) {
                $date = \Carbon\Carbon::parse($dress->delivery_date);
                $cacheKey = 'calendar_availability:' . self::class . ':delivery_date:' . $date->year . ':' . $date->month;
                \Cache::forget($cacheKey);
            }
        });

        static::deleted(function (self $dress) {
            if ($dress->delivery_date) {
                $date = \Carbon\Carbon::parse($dress->delivery_date);
                $cacheKey = 'calendar_availability:' . self::class . ':delivery_date:' . $date->year . ':' . $date->month;
                \Cache::forget($cacheKey);
            }
        });
    }

    // 🔹 SCOPI E METODI CUSTOM
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }
    
public function archive(): void
{
    static::withoutGlobalScope('notArchived')
        ->where('id', $this->id)
        ->update(['archived_at' => now()]);
}

}
