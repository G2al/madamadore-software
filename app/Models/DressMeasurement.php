<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressMeasurement extends Model
{
    use HasFactory;

    /**
     * Mappa ordinata (come nella scheda fotografata):
     * chiave = nome colonna DB, valore = etichetta umana.
     */
    public const ORDERED_MEASURES = [
        'circonferenza_collo'         => 'Circonferenza collo',
        'torace'                      => 'Torace',
        'seno'                        => 'Seno',
        'sotto_seno'                  => 'Sottoseno',
        'vita'                        => 'Vita',
        'bacino'                      => 'Bacino',
        'lunghezza_bacino'           => 'Lunghezza bacino',
        'lunghezza_seno'             => 'L. Seno',
        'distanza_seni'              => 'Distanza Seno',
        'precisapince'               => 'Precisapince',
        'scollo'                     => 'Scollo',
        'scollo_dietro'              => 'Scollo Dietro',
        'lunghezza_vita'             => 'Lung. Vita',
        'lunghezza_vita_dietro'      => 'L. Vita dietro',
        'larghezza_schiena'          => 'Larghezza spalle dietro',
        'inclinazione_spalle'        => 'Inclinazione spalle (max 22°)',
        'larghezza_torace_interno'   => 'Larg. Torace Int.',
        'lunghezza_taglio'           => 'Lung. Taglio',
        'lunghezza_abito'            => 'Lung. Capo',
        'lunghezza_gonna_avanti'     => 'Lung. Gonna Av.',
        'lunghezza_gonna_dietro'     => 'Lung. Gonna D.',
        'lunghezza_gomito'           => 'Lung. Gomito',
        'lunghezza_manica'           => 'Lung. Manica',
        'circonferenza_braccio'      => 'Circ. Braccio',
        'livello_ascellare'          => 'Livello ascellare',
        'lunghezza_pantalone_interno'=> 'L. Pant. Int.',
        'lunghezza_pantalone_esterno'=> 'L. Pant. Est.',
        'lunghezza_ginocchio'        => 'Lung. Ginocchio', // fallback su altezza_ginocchio
        'lunghezza_cavallo'          => 'Cavallo',
        'circonferenza_coscia'       => 'Circ. Coscia',
        'circonferenza_ginocchio'    => 'Circ. Ginocchio',
        'circonferenza_caviglia'     => 'Circ. Caviglia',
        'circonferenza_polso'        => 'Circ. Polso',
        'circonferenza_taglio'       => 'Circ. Taglio',
    ];

    protected $fillable = [
        'dress_id',

        // già esistenti
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

        // nuovi aggiunti dalla migration
        'seno',
        'bacino',
        'lunghezza_bacino',
        'lunghezza_seno',
        'precisapince',
        'scollo',
        'scollo_dietro',
        'lunghezza_vita',
        'lunghezza_vita_dietro',
        'inclinazione_spalle',
        'larghezza_torace_interno',
        'lunghezza_taglio',
        'lunghezza_gonna_avanti',
        'lunghezza_gonna_dietro',
        'lunghezza_gomito',
        'livello_ascellare',
        'lunghezza_pantalone_interno',
        'lunghezza_pantalone_esterno',
        'lunghezza_ginocchio',
        'circonferenza_ginocchio',
        'circonferenza_taglio',
    ];

    protected $casts = [
        // esistenti
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

        // nuovi
        'seno' => 'float',
        'bacino' => 'float',
        'lunghezza_bacino' => 'float',
        'lunghezza_seno' => 'float',
        'precisapince' => 'float',
        'scollo' => 'float',
        'scollo_dietro' => 'float',
        'lunghezza_vita' => 'float',
        'lunghezza_vita_dietro' => 'float',
        'inclinazione_spalle' => 'float',
        'larghezza_torace_interno' => 'float',
        'lunghezza_taglio' => 'float',
        'lunghezza_gonna_avanti' => 'float',
        'lunghezza_gonna_dietro' => 'float',
        'lunghezza_gomito' => 'float',
        'livello_ascellare' => 'float',
        'lunghezza_pantalone_interno' => 'float',
        'lunghezza_pantalone_esterno' => 'float',
        'lunghezza_ginocchio' => 'float',
        'circonferenza_ginocchio' => 'float',
        'circonferenza_taglio' => 'float',
    ];

    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }

    /**
     * Ritorna le 34 misure nell'ordine della scheda, con etichette umane.
     * Usa fallback su 'altezza_ginocchio' se 'lunghezza_ginocchio' è null.
     */
    public function getAllMeasurementsAttribute(): array
    {
        $out = [];

        foreach (self::ORDERED_MEASURES as $field => $label) {
            $value = $this->{$field};

            // fallback specifico ginocchio
            if ($field === 'lunghezza_ginocchio' && is_null($value)) {
                $value = $this->altezza_ginocchio;
            }

            $out[$label] = $value;
        }

        return $out;
    }
}
