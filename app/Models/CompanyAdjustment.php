<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAdjustment extends Model
{
    use HasFactory;

    protected $table = 'company_adjustments';

    /**
     * Mass assignment protection
     */
    protected $fillable = [
        'customer_id',
        'referente',
        'primary_worker_id',
        'status',
        'ritirato',
        'saldato',
        'name',
        'client_price',
        'deposit',
        'total',
        'remaining',
        'profit',
        'delivery_date',
    ];

    /**
     * Casts for numeric fields
     */
    protected $casts = [
        'client_price'  => 'decimal:2',
        'deposit'       => 'decimal:2',
        'total'         => 'decimal:2',
        'remaining'     => 'decimal:2',
        'profit'        => 'decimal:2',
        'delivery_date' => 'date',
        'ritirato'      => 'boolean',
        'saldato'       => 'boolean',
    ];

    /**
     * 🔹 Etichette leggibili per lo stato
     */
    public static function getStatusLabels(): array
    {
        return [
            'confermato'     => 'Completato',
            'in_lavorazione' => 'In Lavorazione',
            'consegnato'     => 'Consegnato',
        ];
    }

    /**
     * 🔹 Colori badge per lo stato
     */
    public static function getStatusColors(): array
    {
        return [
            'confermato'     => 'info',
            'in_lavorazione' => 'warning',
            'consegnato'     => 'success',
        ];
    }

    /**
     * 🔹 Relazioni
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CompanyAdjustmentItem::class);
    }

    public function primaryWorker()
    {
        return $this->belongsTo(Worker::class, 'primary_worker_id');
    }

    /**
     * 🧾 Relazione: spese associate all'aggiusto
     */
    public function expenses()
    {
        return $this->hasMany(CompanyAdjustmentExpense::class);
    }

    /**
     * 🔹 Elimina movimenti di cassa collegati quando l'aggiusto viene eliminato
     */
    protected static function booted()
    {
        static::saved(function ($companyAdjustment) {
            if (
                in_array($companyAdjustment->status, ['confermato', 'consegnato'], true)
                && ($companyAdjustment->wasChanged('status') || $companyAdjustment->wasRecentlyCreated)
            ) {
                $companyAdjustment->items()
                    ->whereNull('completed_at')
                    ->update(['completed_at' => now()]);
            }
        });

        static::deleted(function ($companyAdjustment) {
            \App\Models\Cashbox::where('source', 'CompanyAdjustment #' . $companyAdjustment->id)->delete();
        });
    }
}
