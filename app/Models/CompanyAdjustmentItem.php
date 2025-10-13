<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_adjustment_id',
        'name',
        'description',
    ];

    public function companyAdjustment()
    {
        return $this->belongsTo(CompanyAdjustment::class);
    }
}