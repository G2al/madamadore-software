<?php

namespace App\Http\Controllers;

use App\Models\Dress;
use App\Services\PdfResponseService;
use Illuminate\Routing\Controller;

class PdfController extends Controller
{
    public function modellino(Dress $dress)
    {
        $service = app(\App\Services\DressReceiptService::class);
        $pdf = $service->generateReceipt($dress);
        
        return PdfResponseService::streamPdf($pdf, "modellino-abito-{$dress->id}.pdf");
    }

    public function preventivo(Dress $dress)
    {
        $service = app(\App\Services\DressContractService::class);
        $pdf = $service->generateContract($dress);
        
        return PdfResponseService::streamPdf($pdf, "preventivo-abito-{$dress->id}.pdf");
    }
}