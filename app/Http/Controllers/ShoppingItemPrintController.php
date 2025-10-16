<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use Barryvdh\DomPDF\Facade\Pdf;

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
}
