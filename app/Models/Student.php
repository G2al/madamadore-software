<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cognome',
        'telefono',
        'costo_lezione',
        'saldato',
    ];

    // Accessor: Nome completo
    public function getFullNameAttribute(): string
    {
        return "{$this->nome} {$this->cognome}";
    }

    // Relazioni
    public function presences()
    {
        return $this->hasMany(StudentPresence::class);
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class);
    }
}
