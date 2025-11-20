<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ceremony extends Model
{
    protected $fillable = ['name', 'icon', 'sort_order'];

    public function specialDresses(): HasMany
    {
        return $this->hasMany(SpecialDress::class, 'ceremony_type', 'name');
    }
}
