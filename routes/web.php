<?php

use Illuminate\Support\Facades\Route;
use App\Services\AdjustmentReceiptService;
use App\Models\Adjustment;

// Redirect alla dashboard admin
Route::get('/', function () {
    return redirect('/admin');
});

// Route per la ricevuta (PDF inline)
Route::get('/adjustments/{adjustment}/receipt', function (Adjustment $adjustment, AdjustmentReceiptService $service) {
    $pdf = $service->generateThermalReceipt($adjustment);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="ricevuta-aggiusto-' . $adjustment->id . '.pdf"',
    ]);
})->name('adjustments.receipt');
