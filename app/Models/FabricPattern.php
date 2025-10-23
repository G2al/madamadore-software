<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'fabric_id',
        'name',
        'image',
    ];

    // Relazione: una fantasia appartiene a un tessuto
    public function fabric()
    {
        return $this->belongsTo(Fabric::class);
    }
}