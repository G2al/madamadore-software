<?php

namespace App\Services;

use App\Models\Dress;
use Barryvdh\DomPDF\Facade\Pdf;

class DressReceiptService
{
    public function __construct(
        private readonly DressPdfDataService $dressPdfDataService,
    ) {
    }

    /**
     * Genera il PDF del modellino completo per un Dress.
     */
    public function generateReceipt(Dress $dress)
    {
        return Pdf::loadView('pdf.dress-receipt', [
            'dress' => $dress,
            'document' => $this->dressPdfDataService->build($dress),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Genera la scheda produzione singola.
     */
    public function generateProductionSheet(Dress $dress)
    {
        return Pdf::loadView('pdf.dress-model-production', [
            'dress' => $dress,
            'document' => $this->dressPdfDataService->build($dress),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Genera la scheda tecnica singola.
     */
    public function generateTechnicalSheet(Dress $dress)
    {
        return Pdf::loadView('pdf.dress-model-technical', [
            'dress' => $dress,
            'document' => $this->dressPdfDataService->build($dress),
        ])->setPaper('a4', 'portrait');
    }
}
