<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    use HasFactory;

    protected $table = 'adjustments';

    /**
     * Mass assignment protection
     */
    protected $fillable = [
        'name',
        'client_price',
        'deposit',
        'total',
        'remaining',
        'profit',
        'delivery_date',
    ];

    /**
     * Casts for numeric fields
     */
    protected $casts = [
        'client_price' => 'decimal:2',
        'deposit'      => 'decimal:2',
        'total'        => 'decimal:2',
        'remaining'    => 'decimal:2',
        'profit'       => 'decimal:2',
        'delivery_date'=> 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function booted()
    {
        static::deleted(function ($adjustment) {
            \App\Models\Cashbox::where('source', 'Adjustment #' . $adjustment->id)->delete();
        });
    }

}
