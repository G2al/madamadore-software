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
        'customer_name',
        'phone_number',
        'client_price',
        'deposit',
        'total',
        'remaining',
        'profit',
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
    ];
}
