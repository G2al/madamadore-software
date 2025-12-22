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
    public function printSingle(ShoppingItem $shoppingItem)
    {
        $pdf = Pdf::loadView('pdf.shopping-item-single', [
            'item' => $shoppingItem,
        ]);

        return $pdf->stream('voce-spesa-' . $shoppingItem->id . '.pdf');
    }

    /**
     * 📑 Stampa l’intera lista della spesa
     */
    public function printAll()
    {
        $items = ShoppingItem::orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('pdf.shopping-item-all', [
            'items' => $items,
        ]);

        return $pdf->stream('lista-della-spesa.pdf');
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

        return $pdf->stream('lista-della-spesa-selezionati.pdf');
    }
}
