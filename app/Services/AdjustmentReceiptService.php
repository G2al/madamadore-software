<?php

namespace App\Services;

use App\Models\Adjustment;
use App\Models\CompanyAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;

class AdjustmentReceiptService
{
    /**
     * Genera il PDF della ricevuta standard (A4).
     */
    public function generateReceipt(Adjustment $adjustment)
    {
        return Pdf::loadView('pdf.adjustment-receipt', [
            'adjustment' => $adjustment,
        ]);
    }

    /**
     * Genera il PDF ottimizzato per stampante termica 72mm.
     */
    public function generateThermalReceipt(Adjustment $adjustment)
    {
        return Pdf::loadView('pdf.adjustment-receipt-thermal', [
            'adjustment' => $adjustment,
        ])->setPaper([0, 0, 204, 1000], 'portrait');
        // 204pt ≈ 72mm, altezza flessibile (1000 pt = ~35cm, si adatta al contenuto)
    }

    /**
     * Genera il PDF per ricevuta aziendale completa
     */
    public function generateThermalReceiptCompany(CompanyAdjustment $adjustment)
    {
        return Pdf::loadView('pdf.company-adjustment-receipt', [
            'adjustment' => $adjustment,
        ])->setPaper([0, 0, 204, 1000], 'portrait');
    }

    /**
     * Genera ricevuta PDF per singolo item aziendale
     */
    public function generateSingleItemReceiptCompany(CompanyAdjustment $adjustment, $item)
    {
        return Pdf::loadView('pdf.company-adjustment-single', [
            'adjustment' => $adjustment,
            'item' => $item,
        ])->setPaper([0, 0, 204, 600], 'portrait');
        // Formato termica 72mm, altezza ridotta per singolo item
    }
}