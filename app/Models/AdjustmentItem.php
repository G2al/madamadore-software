<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_id',
        'name',
        'description',
        'worker_id',
        'completed_at',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function adjustment()
    {
        return $this->belongsTo(Adjustment::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    protected static function booted(): void
    {
        static::saved(function (self $item): void {
            $adjustment = $item->adjustment;

            if (
                $adjustment
                && in_array($adjustment->status, ['confermato', 'consegnato'], true)
                && $item->completed_at === null
            ) {
                static::withoutEvents(fn () => $item->update(['completed_at' => now()]));
            }
        });
    }
}
