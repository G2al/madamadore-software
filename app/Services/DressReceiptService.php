<?php

namespace App\Services;

use App\Models\Dress;
use Barryvdh\DomPDF\Facade\Pdf;

class DressReceiptService
{
    /**
     * Genera il PDF della ricevuta per un Dress.
     *
     * @param  \App\Models\Dress  $dress
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateReceipt(Dress $dress)
    {
        return Pdf::loadView('pdf.dress-receipt', [
            'dress' => $dress,
        ]);
    }
}