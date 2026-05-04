<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use App\Services\UnifiedShoppingListPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ShoppingItemPrintController extends Controller
{
    public function __construct(
        private readonly UnifiedShoppingListPdfService $shoppingListPdfService,
    ) {
    }

    /**
     * 📄 Stampa singolo elemento della lista della spesa
     */
    public function printSingle(Request $request, ShoppingItem $shoppingItem)
    {
        $pdf = Pdf::loadView('pdf.shopping-item-single', [
            'item' => $shoppingItem,
        ]);

        $filename = 'voce-spesa-' . $shoppingItem->id . '.pdf';

        if ($request->boolean('autoPrint')) {
            return $this->autoPrint($pdf, $filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * 📑 Stampa l’intera lista della spesa
     */
    public function printAll(Request $request)
    {
        $pdf = Pdf::loadView(
            'pdf.shopping-list-unified',
            $this->shoppingListPdfService->buildUnified(),
        );

        $filename = 'lista-della-spesa-unica.pdf';

        if ($request->boolean('autoPrint')) {
            return $this->autoPrint($pdf, $filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * 🖨️ Stampa solo gli elementi selezionati (bulk action)
     */
    public function printSelected(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn (string $id) => trim($id))
            ->filter()
            ->map(fn (string $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $payload = filled($ids)
            ? $this->shoppingListPdfService->buildManualOnly($ids)
            : [
                'title' => 'Lista della Spesa',
                'subtitle' => 'Nessuna voce selezionata',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'supplierGroups' => collect(),
                'overallTotalCost' => 0,
                'overallRows' => 0,
            ];

        $pdf = Pdf::loadView('pdf.shopping-list-unified', $payload);

        $filename = 'lista-della-spesa-selezionati.pdf';

        if ($request->boolean('autoPrint')) {
            return $this->autoPrint($pdf, $filename);
        }

        return $pdf->stream($filename);
    }

    protected function autoPrint(\Barryvdh\DomPDF\PDF $pdf, string $filename)
    {
        $base64Pdf = base64_encode($pdf->output());

        return response()->view('pdf.auto-print', [
            'pdfData'  => 'data:application/pdf;base64,' . $base64Pdf,
            'filename' => $filename,
        ]);
    }
}
