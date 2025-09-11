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
        'manufacturing_price',
        'deposit',
        'remaining',
        'status',
        'manual_client_price', 
        'use_manual_price',
    ];

    protected $casts = [
        'ceremony_date' => 'date',
        'delivery_date' => 'date',
        'deposit' => 'decimal:2',
        'manufacturing_price' => 'decimal:2',
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
        return $this->fabrics->sum('client_price') + 
            $this->extras->sum('cost') + 
            ($this->manufacturing_price ?? 0);  // <- AGGIUNGI QUESTA RIGA
    }

    public function getProfitAttribute(): float
    {
        return $this->total_client_price - $this->total_purchase_cost;
    }

    public function getCustomerInfoAttribute(): string
    {
        return "<strong>{$this->customer_name}</strong><br>
                <small class='text-gray-500'>{$this->phone_number}</small>";
    }

    public function getCeremonyInfoAttribute(): string
    {
        $date = $this->ceremony_date?->format('d/m/Y') ?? '-';
        $type = $this->ceremony_type ?? '-';

        return "<strong>{$date}</strong><br>
                <small class='text-gray-500'>{$type}</small>";
    }

}