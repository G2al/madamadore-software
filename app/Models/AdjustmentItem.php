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
        'price', // ← AGGIUNTO
    ];

    protected $casts = [
        'price' => 'decimal:2', // ← AGGIUNTO
    ];

    public function adjustment()
    {
        return $this->belongsTo(Adjustment::class);
    }
}