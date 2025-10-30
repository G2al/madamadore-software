<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialDressMeasurement extends Model
{
    protected $fillable = [
        'special_dress_id',

        // legacy
        'spalle','torace','sotto_seno','vita','fianchi','lunghezza_busto',
        'lunghezza_manica','circonferenza_braccio','circonferenza_polso',
        'altezza_totale','lunghezza_abito','lunghezza_gonna','circonferenza_collo',
        'larghezza_schiena','altezza_seno','distanza_seni','circonferenza_coscia',
        'lunghezza_cavallo','altezza_ginocchio','circonferenza_caviglia',

        // nuovi
        'seno','bacino','lunghezza_bacino','lunghezza_seno','precisapince',
        'scollo','scollo_dietro','lunghezza_vita','lunghezza_vita_dietro',
        'inclinazione_spalle','larghezza_torace_interno','lunghezza_taglio',
        'lunghezza_gonna_avanti','lunghezza_gonna_dietro','lunghezza_gomito',
        'livello_ascellare','lunghezza_pantalone_interno','lunghezza_pantalone_esterno',
        'lunghezza_ginocchio','circonferenza_ginocchio','circonferenza_taglio',
    ];

    protected $casts = [
        // tutti i float
        'spalle' => 'float','torace' => 'float','sotto_seno' => 'float','vita' => 'float','fianchi' => 'float',
        'lunghezza_busto' => 'float','lunghezza_manica' => 'float','circonferenza_braccio' => 'float',
        'circonferenza_polso' => 'float','altezza_totale' => 'float','lunghezza_abito' => 'float',
        'lunghezza_gonna' => 'float','circonferenza_collo' => 'float','larghezza_schiena' => 'float',
        'altezza_seno' => 'float','distanza_seni' => 'float','circonferenza_coscia' => 'float',
        'lunghezza_cavallo' => 'float','altezza_ginocchio' => 'float','circonferenza_caviglia' => 'float',
        'seno' => 'float','bacino' => 'float','lunghezza_bacino' => 'float','lunghezza_seno' => 'float',
        'precisapince' => 'float','scollo' => 'float','scollo_dietro' => 'float','lunghezza_vita' => 'float',
        'lunghezza_vita_dietro' => 'float','inclinazione_spalle' => 'float','larghezza_torace_interno' => 'float',
        'lunghezza_taglio' => 'float','lunghezza_gonna_avanti' => 'float','lunghezza_gonna_dietro' => 'float',
        'lunghezza_gomito' => 'float','livello_ascellare' => 'float','lunghezza_pantalone_interno' => 'float',
        'lunghezza_pantalone_esterno' => 'float','lunghezza_ginocchio' => 'float','circonferenza_ginocchio' => 'float',
        'circonferenza_taglio' => 'float',
    ];

    public const ORDERED_MEASURES = \App\Models\DressMeasurement::ORDERED_MEASURES;

    public function specialDress(): BelongsTo
    {
        return $this->belongsTo(SpecialDress::class);
    }
}
