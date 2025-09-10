<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashbox extends Model
{
    protected $table = 'cashbox';

    protected $fillable = [
        'type',
        'source',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
