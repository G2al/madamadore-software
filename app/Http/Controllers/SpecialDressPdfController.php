<?php

namespace App\Http\Controllers;

use App\Models\SpecialDress;
use App\Services\PdfResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SpecialDressPdfController extends Controller
{
    public function modellino(SpecialDress $record)
    {
        $pdf = Pdf::loadView('pdf.special.modellino', [
            'dress' => $record,
        ])->setPaper('a4');

        return $pdf->stream('modellino-special-' . $record->id . '.pdf');
    }

    public function preventivo(SpecialDress $record)
    {
        $pdf = Pdf::loadView('pdf.special.preventivo', [
            'dress' => $record,
        ])->setPaper('a4');

        return $pdf->stream('preventivo-special-' . $record->id . '.pdf');
    }

    /**
     * PDF con tutti gli abiti speciali da consegnare in un determinato mese.
     */
    public function monthlySpecialDresses(int $year, int $month)
    {
        $referenceDate = Carbon::createFromDate($year, $month, 1)->locale('it');

        $dresses = SpecialDress::query()
            ->whereYear('delivery_date', $year)
            ->whereMonth('delivery_date', $month)
            ->orderBy('delivery_date')
            ->orderBy('customer_name')
            ->get();

        $statuses = config('dress.statuses', []);

        $monthLabel = $referenceDate->translatedFormat('F Y');

        $pdf = Pdf::loadView('pdf.special.dresses_monthly', [
                'dresses'       => $dresses,
                'referenceDate' => $referenceDate,
                'monthLabel'    => $monthLabel,
                'year'          => $year,
                'month'         => $month,
                'statuses'      => $statuses,
            ])
            ->setPaper('a4', 'portrait');

        $filename = sprintf(
            'abiti-speciali-consegna-%s-%d.pdf',
            str_pad($month, 2, '0', STR_PAD_LEFT),
            $year
        );

        return PdfResponseService::streamPdf($pdf, $filename);
    }
}
