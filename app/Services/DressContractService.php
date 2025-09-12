<?php

namespace App\Services;

use App\Models\Dress;
use Barryvdh\DomPDF\Facade\Pdf;

class DressContractService
{
    /**
     * Genera il PDF del contratto per un Dress.
     *
     * @param  \App\Models\Dress  $dress
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateContract(Dress $dress)
    {
        return Pdf::loadView('pdf.dress-contract', [
            'dress' => $dress,
        ]);
    }
}