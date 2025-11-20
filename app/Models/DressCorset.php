<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DressCorset extends Model
{
    protected $fillable = [
        'dress_id',
        'pinza_vita_davanti',
        'pinza_vita_lato',
        'pinza_vita_dietro',
        'pinza_fianchi_davanti',
        'pinza_fianchi_lato',
        'pinza_fianchi_dietro',
        'linea_sottoseno_davanti',
        'linea_sottoseno_lato',
        'linea_sottoseno_dietro',
    ];

    protected $casts = [
        'pinza_vita_davanti' => 'float',
        'pinza_vita_lato' => 'float',
        'pinza_vita_dietro' => 'float',
        'pinza_fianchi_davanti' => 'float',
        'pinza_fianchi_lato' => 'float',
        'pinza_fianchi_dietro' => 'float',
        'linea_sottoseno_davanti' => 'float',
        'linea_sottoseno_lato' => 'float',
        'linea_sottoseno_dietro' => 'float',
    ];

    public function dress(): BelongsTo
    {
        return $this->belongsTo(Dress::class);
    }
}
