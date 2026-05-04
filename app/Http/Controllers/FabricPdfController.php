<?php

namespace App\Http\Controllers;

use App\Models\DressFabric;
use App\Services\UnifiedShoppingListPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FabricPdfController extends Controller
{
    public function __construct(
        private readonly UnifiedShoppingListPdfService $shoppingListPdfService,
    ) {
    }

    public function print(Request $request)
    {
        $colorCode = $request->query('color');
        $download = $request->boolean('download');

        if ($colorCode === null) {
            $payload = $this->shoppingListPdfService->buildUnified();
            $pdf = Pdf::loadView('pdf.shopping-list-unified', $payload);
            $filename = 'lista-della-spesa-unica-' . now()->format('Y-m-d') . '.pdf';

            if ($download) {
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename);
            }

            return $pdf->stream($filename);
        }

        $query = DressFabric::query()
            ->pendingPurchase()
            ->with(['dress:id,customer_name,delivery_date'])
            ->orderBy('supplier', 'asc')
            ->orderBy('name', 'asc')
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

        if ($download) {
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
        }

        return $pdf->stream($filename);
    }
}
