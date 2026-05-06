<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\UnifiedShoppingListPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SupplierShoppingListController extends Controller
{
    public function __construct(
        private readonly UnifiedShoppingListPdfService $shoppingListPdfService,
    ) {
    }

    public function print(Supplier $supplier)
    {
        $payload = $this->shoppingListPdfService->buildForSupplier($supplier);
        $pdf = Pdf::loadView('pdf.shopping-list-unified', $payload);

        return $pdf->stream('lista-spesa-' . str($supplier->name)->slug() . '.pdf');
    }

    public function shared(Request $request, Supplier $supplier)
    {
        abort_unless($request->hasValidSignature(), 401);

        $payload = $this->shoppingListPdfService->buildForSupplier($supplier);
        $pdf = Pdf::loadView('pdf.shopping-list-unified', $payload);

        return $pdf->stream('lista-spesa-' . str($supplier->name)->slug() . '.pdf');
    }
}
