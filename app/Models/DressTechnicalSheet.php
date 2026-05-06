<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressTechnicalSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'model_name',
        'line_name',
        'garment_type',
        'client_notes',
        'technical_description',
        'production_notes',
        'construction_notes',
        'materials_notes',
        'accessories_notes',
        'measurements_responsible',
        'nb_notes',
        'neckline_details',
        'sleeve_details',
        'bodice_details',
        'back_details',
        'closure_details',
        'main_fabric_name',
        'main_fabric_composition',
        'main_fabric_color',
        'sleeve_fabric_name',
        'sleeve_fabric_composition',
        'sleeve_fabric_color',
        'technical_drawing_image',
        'front_view_image',
        'back_view_image',
        'neckline_detail_image',
        'sleeve_detail_image',
        'bodice_detail_image',
        'back_detail_image',
        'closure_detail_image',
    ];

    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }
}
