<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'nome',
        'cognome',
        'telefono',
    ];

    /**
     * Una persona può avere più presenze
     */
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * Accessor per ottenere il nome completo
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->nome} {$this->cognome}";
    }
}
