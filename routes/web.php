<?php

use Illuminate\Support\Facades\Route;
use App\Services\AdjustmentReceiptService;
use App\Models\Adjustment;
use App\Models\CompanyAdjustment;
use App\Http\Controllers\ShoppingItemPrintController;
use App\Http\Controllers\SpecialDressPdfController;

// Redirect alla dashboard admin
Route::get('/', function () {
    return redirect('/admin');
});

// ===============================================
// 🧾 RICEVUTE AGGIUSTI
// ===============================================

// Ricevuta aggiusti normali (PDF inline)
Route::get('/adjustments/{adjustment}/receipt', function (Adjustment $adjustment, AdjustmentReceiptService $service) {
    $pdf = $service->generateThermalReceipt($adjustment);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="ricevuta-aggiusto-' . $adjustment->id . '.pdf"',
    ]);
})->name('adjustments.receipt');

// Ricevuta aggiusti aziendali (PDF inline)
Route::get('/company-adjustments/{companyAdjustment}/receipt', function (CompanyAdjustment $companyAdjustment, AdjustmentReceiptService $service) {
    $pdf = $service->generateThermalReceiptCompany($companyAdjustment);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="ricevuta-aggiusto-aziendale-' . $companyAdjustment->id . '.pdf"',
    ]);
})->name('company-adjustments.receipt');

// Ricevuta singolo item SOLO per aggiusti aziendali
Route::get('/company-adjustments/{companyAdjustment}/receipt/item/{item}', function (CompanyAdjustment $companyAdjustment, $item, AdjustmentReceiptService $service) {
    $adjustmentItem = $companyAdjustment->items()->findOrFail($item);
    $pdf = $service->generateSingleItemReceiptCompany($companyAdjustment, $adjustmentItem);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="ricevuta-singola-aziendale-' . $companyAdjustment->id . '-' . $item . '.pdf"',
    ]);
})->name('company-adjustments.single-receipt');

// ===============================================
// 👗 PDF ABITI + LISTA SPESA + CALENDARIO
// ===============================================
Route::middleware(['auth'])->group(function () {
    // 📄 PDF Abiti standard
    Route::get('/pdf/modellino/{dress}', [App\Http\Controllers\PdfController::class, 'modellino'])
        ->name('pdf.modellino');

    Route::get('/pdf/preventivo/{dress}', [App\Http\Controllers\PdfController::class, 'preventivo'])
        ->name('pdf.preventivo');

    // 🌟 PDF Abiti Speciali
    Route::get('/pdf/special/modellino/{record}', [SpecialDressPdfController::class, 'modellino'])
        ->name('pdf.special.modellino');

    Route::get('/pdf/special/preventivo/{record}', [SpecialDressPdfController::class, 'preventivo'])
        ->name('pdf.special.preventivo');

    // 📦 Lista della Spesa - Stampa PDF
    Route::get('/shopping-items/{shoppingItem}/print', [ShoppingItemPrintController::class, 'printSingle'])
        ->name('shopping-items.print.single');

    Route::get('/shopping-items/print/all', [ShoppingItemPrintController::class, 'printAll'])
        ->name('shopping-items.print.all');

    // 🗓️ API per calendario consegne
    Route::get('/api/delivery-calendar', [App\Http\Controllers\Api\DeliveryCalendarController::class, 'getDeliveryDates'])
        ->name('api.delivery-calendar');

    // 🗓️ Calendario disponibilità (abiti + aggiusti)
    Route::post('/admin/calendar/availability', [App\Http\Controllers\Admin\CalendarController::class, 'getAvailability'])
        ->name('admin.calendar.availability');
});
