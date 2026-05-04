<?php

namespace App\Services;

use App\Models\Dress;
use Barryvdh\DomPDF\Facade\Pdf;

class DressContractService
{
    public function __construct(
        private readonly DressPdfDataService $dressPdfDataService,
    ) {
    }

    /**
     * Genera il PDF del preventivo per un Dress.
     */
    public function generateContract(Dress $dress)
    {
        return Pdf::loadView('pdf.dress-preventivo', [
            'dress' => $dress,
            'document' => $this->dressPdfDataService->build($dress),
        ])->setPaper('a4', 'portrait');
    }
}
