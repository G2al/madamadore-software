<?php

namespace App\Services;

use Illuminate\Http\Response;
use Barryvdh\DomPDF\PDF;

class PdfResponseService
{
    public static function streamPdf(PDF $pdf, string $filename): Response
    {
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
    
    public static function downloadPdf(PDF $pdf, string $filename): Response
    {
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}