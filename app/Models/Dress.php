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
        'deposit',
        'status',
    ];

    protected $casts = [
        'ceremony_date' => 'date',
        'delivery_date' => 'date',
        'deposit' => 'decimal:2',
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

    // Calculated attributes
    public function getTotalPurchaseCostAttribute(): float
    {
        return $this->fabrics->sum('purchase_price');
    }

    public function getTotalClientPriceAttribute(): float
    {
        return $this->fabrics->sum('client_price') + $this->extras->sum('cost');
    }

    public function getProfitAttribute(): float
    {
        return $this->total_client_price - $this->total_purchase_cost;
    }
}