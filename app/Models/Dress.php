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
        'manual_client_price',
        'use_manual_price',
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
    ];

    // Relationships
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

    // Totali calcolati (usati per UI)
    public function getTotalPurchaseCostAttribute(): float
    {
        // costo d'acquisto = metri * prezzo_acquisto
        return $this->fabrics->sum(fn($f) => (float) $f->meters * (float) $f->purchase_price);
    }

    public function getTotalClientPriceAttribute(): float
    {
        // prezzo cliente = (metri * prezzo_cliente_unitario) + extra + manifattura
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

    /**
     * Ricalcola i valori economici e (se $persist = true) li salva in DB.
     */
    public function recalcFinancials(bool $persist = false): array
    {
        // Prepara array per il service
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

        // Se è attivo il prezzo manuale, sovrascrivi il totale & ricalcola profitto/remaining
        if ($this->use_manual_price && $this->manual_client_price !== null) {
            $calc['total_client_price'] = (float) $this->manual_client_price;
            $calc['total_profit'] = $calc['total_client_price'] - $calc['total_purchase_cost'];
            $calc['remaining'] = $calc['total_client_price'] - (float) ($this->deposit ?? 0);
        }

        if ($persist) {
            // Scrive nei campi DB (senza scatenare eventi → no loop)
            $this->forceFill([
                'total_purchase_cost' => $calc['total_purchase_cost'],
                'total_client_price'  => $calc['total_client_price'],
                'total_profit'        => $calc['total_profit'],
                'remaining'           => $calc['remaining'],
            ])->saveQuietly();
        }

        return $calc;
    }

    /**
     * Dopo ogni salvataggio del Dress, ricalcola e persiste i totali.
     * (Nessuna modifica alle pagine Create/Edit richiesta)
     */
    protected static function booted(): void
    {
        static::saved(function (self $dress) {
            // assicurati di avere le relazioni in memoria
            $dress->loadMissing('fabrics', 'extras');

            // calcola e PERSISTE (saveQuietly → non rilancia eventi)
            $dress->recalcFinancials(true);
        });
    }
}
