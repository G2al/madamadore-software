<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'spalle',
        'torace',
        'sotto_seno',
        'vita',
        'fianchi',
        'lunghezza_busto',
        'lunghezza_manica',
        'circonferenza_braccio',
        'circonferenza_polso',
        'altezza_totale',
        'lunghezza_abito',
        'lunghezza_gonna',
        'circonferenza_collo',
        'larghezza_schiena',
        'altezza_seno',
        'distanza_seni',
        'circonferenza_coscia',
        'lunghezza_cavallo',
        'altezza_ginocchio',
        'circonferenza_caviglia',
    ];

    protected $casts = [
        'spalle' => 'float',
        'torace' => 'float',
        'sotto_seno' => 'float',
        'vita' => 'float',
        'fianchi' => 'float',
        'lunghezza_busto' => 'float',
        'lunghezza_manica' => 'float',
        'circonferenza_braccio' => 'float',
        'circonferenza_polso' => 'float',
        'altezza_totale' => 'float',
        'lunghezza_abito' => 'float',
        'lunghezza_gonna' => 'float',
        'circonferenza_collo' => 'float',
        'larghezza_schiena' => 'float',
        'altezza_seno' => 'float',
        'distanza_seni' => 'float',
        'circonferenza_coscia' => 'float',
        'lunghezza_cavallo' => 'float',
        'altezza_ginocchio' => 'float',
        'circonferenza_caviglia' => 'float',
    ];

    // Relationships
    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }

    // Helper method per ottenere tutte le misure come array
    public function getAllMeasurementsAttribute(): array
    {
        return [
            'Spalle' => $this->spalle,
            'Torace' => $this->torace,
            'Sotto Seno' => $this->sotto_seno,
            'Vita' => $this->vita,
            'Fianchi' => $this->fianchi,
            'Lunghezza Busto' => $this->lunghezza_busto,
            'Lunghezza Manica' => $this->lunghezza_manica,
            'Circonferenza Braccio' => $this->circonferenza_braccio,
            'Circonferenza Polso' => $this->circonferenza_polso,
            'Altezza Totale' => $this->altezza_totale,
            'Lunghezza Abito' => $this->lunghezza_abito,
            'Lunghezza Gonna' => $this->lunghezza_gonna,
            'Circonferenza Collo' => $this->circonferenza_collo,
            'Larghezza Schiena' => $this->larghezza_schiena,
            'Altezza Seno' => $this->altezza_seno,
            'Distanza Seni' => $this->distanza_seni,
            'Circonferenza Coscia' => $this->circonferenza_coscia,
            'Lunghezza Cavallo' => $this->lunghezza_cavallo,
            'Altezza Ginocchio' => $this->altezza_ginocchio,
            'Circonferenza Caviglia' => $this->circonferenza_caviglia,
        ];
    }
}