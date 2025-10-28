<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appuntamento extends Model
{
    use HasFactory;

    protected $table = 'appuntamenti';

    protected $fillable = [
        'nome',
        'cognome',
        'telefono',
        'data_appuntamento',
        'ora_appuntamento',
        'descrizione',
        'stato',
        'completed_at',
    ];

protected $casts = [
    'data_appuntamento' => 'date',
    'ora_appuntamento'  => 'string',
    'completed_at'      => 'datetime',
];

    /**
     * Segna l'appuntamento come completato manualmente.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'stato' => 'fatto',
            'completed_at' => now(),
        ]);
    }
}
