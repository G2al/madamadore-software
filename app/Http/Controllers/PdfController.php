<?php

namespace App\Http\Controllers;

use App\Models\Dress;
use App\Services\PdfResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

    /**
     * PDF con tutti gli abiti da consegnare in un determinato mese.
     *
     * @param  int  $year
     * @param  int  $month
     */
    public function monthlyDresses(int $year, int $month)
    {
        // Data di riferimento (1° giorno del mese)
        $referenceDate = Carbon::createFromDate($year, $month, 1)->locale('it');

        // Abiti con consegna nel mese richiesto
        $dresses = Dress::query()
            ->whereYear('delivery_date', $year)
            ->whereMonth('delivery_date', $month)
            ->orderBy('delivery_date')
            ->orderBy('customer_name')
            ->get();

        $statuses = config('dress.statuses', []);

        $monthLabel = $referenceDate->translatedFormat('F Y'); // es: "gennaio 2025"

        $pdf = Pdf::loadView('pdf.dresses_monthly', [
                'dresses'       => $dresses,
                'referenceDate' => $referenceDate,
                'monthLabel'    => $monthLabel,
                'year'          => $year,
                'month'         => $month,
                'statuses'      => $statuses,
            ])
            ->setPaper('a4', 'portrait');

        $filename = sprintf(
            'abiti-consegna-%s-%d.pdf',
            str_pad($month, 2, '0', STR_PAD_LEFT),
            $year
        );

        return PdfResponseService::streamPdf($pdf, $filename);
    }
}
