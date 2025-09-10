<?php

namespace App\Services;

use App\Models\Adjustment;
use Barryvdh\DomPDF\Facade\Pdf;

class AdjustmentReceiptService
{
    /**
     * Genera il PDF della ricevuta per un Adjustment.
     *
     * @param  \App\Models\Adjustment  $adjustment
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateReceipt(Adjustment $adjustment)
    {
        return Pdf::loadView('pdf.adjustment-receipt', [
            'adjustment' => $adjustment,
        ]);
    }
}