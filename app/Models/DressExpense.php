<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DressExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'name',
        'photo_path',
        'price',
    ];

    public function dress()
    {
        return $this->belongsTo(Dress::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }
}
