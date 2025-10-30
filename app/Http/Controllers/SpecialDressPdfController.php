<?php

namespace App\Http\Controllers;

use App\Models\SpecialDress;
use Barryvdh\DomPDF\Facade\Pdf;

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
}
