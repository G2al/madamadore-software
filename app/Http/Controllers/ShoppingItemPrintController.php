<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ShoppingItemPrintController extends Controller
{
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
        $items = ShoppingItem::orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('pdf.shopping-item-all', [
            'items' => $items,
        ]);

        $filename = 'lista-della-spesa.pdf';

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

        $items = ShoppingItem::query()
            ->when(
                filled($ids),
                fn ($query) => $query->whereIn('id', $ids),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('pdf.shopping-item-all', [
            'items' => $items,
        ]);

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
