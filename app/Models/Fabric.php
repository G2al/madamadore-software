<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'color_code',
        'supplier',
        'purchase_price',
        'client_price',
        'image',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'client_price' => 'decimal:2',
    ];
}
