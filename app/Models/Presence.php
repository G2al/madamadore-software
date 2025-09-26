<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    protected $table = 'presences';

    protected $fillable = [
        'person_id',
        'date',
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
