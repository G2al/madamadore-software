<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialDress extends Model
{
    protected $fillable = [
        'customer_name',
        'phone_number',
        'ceremony_type',
        'delivery_date',
        'sketch_image',
        'final_image',
        'notes',
        'total_client_price',
        'deposit',
        'remaining',
        'status',
        'archived_at',
        'ritirato',
        'saldato',
        'payment_method',
        'custom_measurements', // ⬅️ nuovo (JSON)
    ];

    protected $casts = [
        'delivery_date'        => 'date',
        'total_client_price'   => 'decimal:2',
        'deposit'              => 'decimal:2',
        'remaining'            => 'decimal:2',
        'ritirato'             => 'boolean',
        'saldato'              => 'boolean',
        'archived_at'          => 'datetime',
        'custom_measurements'  => 'array', // ⬅️ nuovo (JSON → array)
    ];

    // Relazione misure standard (tabella dedicata)
    public function measurements(): HasOne
    {
        return $this->hasOne(SpecialDressMeasurement::class);
    }

    // Relazione misure personalizzate
    public function customMeasurements(): HasMany
    {
        return $this->hasMany(SpecialDressCustomMeasurement::class);
    }

    // Relazione verso la festività
    public function ceremony(): BelongsTo
    {
        return $this->belongsTo(Ceremony::class, 'ceremony_type', 'name');
    }

    // Recalcolo semplice: remaining = price - deposit (min 0)
    public function recalcFinancials(bool $persist = false): array
    {
        $price     = (float) ($this->total_client_price ?? 0);
        $deposit   = (float) ($this->deposit ?? 0);
        $remaining = max($price - $deposit, 0);

        if ($persist) {
            $this->forceFill(['remaining' => $remaining])->saveQuietly();
        }

        return [
            'total_client_price' => $price,
            'deposit'            => $deposit,
            'remaining'          => $remaining,
        ];
    }

    protected static function booted(): void
    {
        // Escludi archiviati dalle query di default
        static::addGlobalScope('notArchived', fn (Builder $q) => $q->whereNull('archived_at'));

        // Ricalcolo automatico remaining
        static::saving(function (self $dress) {
            $calc = $dress->recalcFinancials(false);
            $dress->remaining = $calc['remaining'];
        });
    }

    public function scopeArchived(Builder $query): Builder
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
