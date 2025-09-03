<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'description',
        'cost',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    // Relationships
    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }
}