<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fabric_id',
        'name',
        'price',
        'quantity',
        'unit_type',
        'supplier',
        'photo_path',
        'purchase_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'purchase_date' => 'datetime',
    ];

    public function isPaid(): bool
    {
        return !is_null($this->purchase_date);
    }

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }
}
