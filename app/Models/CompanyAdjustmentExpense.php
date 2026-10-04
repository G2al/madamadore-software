<?php

namespace App\Models;

use App\Services\ShoppingItemSourceSyncService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAdjustmentExpense extends Model
{
    use HasFactory;

    /**
     * Attributi assegnabili in massa
     */
    protected $fillable = [
        'company_adjustment_id',
        'name',
        'photo_path',
        'price',
        'color_code',
        'quantity',
        'unit_type',
        'supplier_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(fn (self $expense) => app(ShoppingItemSourceSyncService::class)->sync($expense));
        static::deleted(fn (self $expense) => app(ShoppingItemSourceSyncService::class)->delete($expense));
    }

    /**
     * Relazione: ogni spesa appartiene a un singolo aggiusto aziendale
     */
    public function companyAdjustment()
    {
        return $this->belongsTo(CompanyAdjustment::class);
    }

    public function supplierRecord(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Accessor per ottenere l'URL completo della foto
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/'.$this->photo_path)
            : null;
    }
}
