<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialDressCustomMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'special_dress_id',
        'label',
        'value',
        'unit',
        'notes',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    // Relationships
    public function specialDress(): BelongsTo
    {
        return $this->belongsTo(SpecialDress::class);
    }

    // Accessor per formattare la misura con unità
    public function getFormattedValueAttribute(): string
    {
        if ($this->value === null) {
            return '-';
        }

        return $this->value . ' ' . $this->unit;
    }
}
