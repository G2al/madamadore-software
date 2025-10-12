<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentExpense extends Model
{
    use HasFactory;

    /**
     * Attributi assegnabili in massa
     */
    protected $fillable = [
        'adjustment_id',
        'name',
        'photo_path',
        'price',
    ];

    /**
     * Relazione: ogni spesa appartiene a un singolo aggiusto
     */
    public function adjustment()
    {
        return $this->belongsTo(Adjustment::class);
    }

    /**
     * Accessor per ottenere l’URL completo della foto
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }
}
