<?php

namespace App\Http\Controllers;

use App\Models\DressFabric;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FabricPdfController extends Controller
{
    public function print(Request $request)
    {
        $colorCode = $request->query('color');
        $autoPrint = $request->boolean('autoPrint');

        $query = DressFabric::query()
            ->with(['dress:id,customer_name,status,delivery_date'])
            ->whereHas('dress', fn ($q) => $q->whereIn('status', ['confermato', 'da_tagliare']))
            ->orderBy('supplier', 'asc')
            ->orderBy('color_code', 'asc');

        if ($colorCode !== null) {
            $query->where('color_code', $colorCode);
        }

        $fabrics = $query->get();

        $totalCost = $fabrics->sum(fn ($item) => (float) $item->meters * (float) $item->purchase_price);

        $pdf = Pdf::loadView('pdf.fabric-requirements', [
            'fabrics'     => $fabrics,
            'totalCost'   => $totalCost,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'colorCode'   => $colorCode,
        ]);

        $filename = $colorCode
            ? "lista-tessuti-{$colorCode}-" . now()->format('Y-m-d') . ".pdf"
            : "lista-acquisti-tessuti-" . now()->format('Y-m-d') . ".pdf";

        if ($autoPrint) {
            $base64Pdf = base64_encode($pdf->output());

            return response()->view('pdf.auto-print', [
                'pdfData'  => 'data:application/pdf;base64,' . $base64Pdf,
                'filename' => $filename,
            ]);
        }

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}
