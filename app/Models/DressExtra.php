<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'description',
        'cost',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    // Relationships
    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }

    // Hooks: ricalcolo automatico dei totali dell'abito
    protected static function booted(): void
    {
        // create & update
        static::saved(function (self $extra) {
            $dress = $extra->dress;
            if ($dress) {
                $dress->loadMissing('fabrics', 'extras');
                $dress->recalcFinancials(true); // persiste total_purchase_cost, total_client_price, total_profit, remaining
            }
        });

        // delete
        static::deleted(function (self $extra) {
            $dress = $extra->dress;
            if ($dress) {
                $dress->loadMissing('fabrics', 'extras');
                $dress->recalcFinancials(true);
            }
        });
    }
}
