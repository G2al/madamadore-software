<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    protected $table = 'presences';

    public const SHIFT_TYPES = [
        'full_day' => 'Giorno intero',
        'half_day' => 'Mezza giornata',
    ];

    protected $fillable = [
        'person_id',
        'date',
        'shift_type',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Una presenza appartiene a una persona
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
