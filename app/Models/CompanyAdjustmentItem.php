<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_adjustment_id',
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

    public function companyAdjustment()
    {
        return $this->belongsTo(CompanyAdjustment::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    protected static function booted(): void
    {
        static::saved(function (self $item): void {
            $companyAdjustment = $item->companyAdjustment;

            if (
                $companyAdjustment
                && in_array($companyAdjustment->status, ['confermato', 'consegnato'], true)
                && $item->completed_at === null
            ) {
                static::withoutEvents(fn () => $item->update(['completed_at' => now()]));
            }
        });
    }
}
