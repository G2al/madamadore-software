<?php

use Illuminate\Support\Facades\Route;
use App\Services\AdjustmentReceiptService;
use App\Models\Adjustment;
use App\Models\CompanyAdjustment;
use App\Http\Controllers\ShoppingItemPrintController;
use App\Http\Controllers\SpecialDressPdfController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SupplierShoppingListController;
use App\Http\Controllers\AdjustmentPrintController;

// Redirect alla dashboard admin
Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/suppliers/{supplier}/shopping-list/shared', [SupplierShoppingListController::class, 'shared'])
    ->middleware('signed')
    ->name('suppliers.shopping-list.shared');

// Link condivisibile via WhatsApp: deve aprirsi anche fuori dal gestionale.
Route::get('/shopping-items/print/selected', [ShoppingItemPrintController::class, 'printSelected'])
    ->name('shopping-items.print.selected');

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
    Route::get('/pdf/modellino/{dress}', [PdfController::class, 'modellino'])
        ->name('pdf.modellino');

    Route::get('/pdf/preventivo/{dress}', [PdfController::class, 'preventivo'])
        ->name('pdf.preventivo');

    Route::get('/pdf/modellino/{dress}/produzione', [PdfController::class, 'productionSheet'])
        ->name('pdf.modellino.production');

    Route::get('/pdf/modellino/{dress}/tecnica', [PdfController::class, 'technicalSheet'])
        ->name('pdf.modellino.technical');

    // 📄 PDF Abiti per mese (consegna)
    Route::get('/pdf/dresses/monthly/{year}/{month}', [PdfController::class, 'monthlyDresses'])
        ->whereNumber('year')
        ->whereNumber('month')
        ->name('pdf.dresses.monthly');

    Route::get('/pdf/adjustments/day/{date}', [AdjustmentPrintController::class, 'dailyAdjustments'])
        ->name('pdf.adjustments.day');

    Route::get('/pdf/adjustments/week/{startDate}/{endDate}', [AdjustmentPrintController::class, 'weeklyAdjustments'])
        ->name('pdf.adjustments.week');

    Route::get('/pdf/company-adjustments/day/{date}', [AdjustmentPrintController::class, 'dailyCompanyAdjustments'])
        ->name('pdf.company-adjustments.day');

    Route::get('/pdf/company-adjustments/week/{startDate}/{endDate}', [AdjustmentPrintController::class, 'weeklyCompanyAdjustments'])
        ->name('pdf.company-adjustments.week');

    // 🌟 PDF Abiti Speciali
    Route::get('/pdf/special/modellino/{record}', [SpecialDressPdfController::class, 'modellino'])
        ->name('pdf.special.modellino');

    Route::get('/pdf/special/preventivo/{record}', [SpecialDressPdfController::class, 'preventivo'])
        ->name('pdf.special.preventivo');

    // PDF Abiti Speciali per mese (consegna)
    Route::get('/pdf/special/dresses/monthly/{year}/{month}', [SpecialDressPdfController::class, 'monthlySpecialDresses'])
        ->whereNumber('year')
        ->whereNumber('month')
        ->name('pdf.special.dresses.monthly');

    Route::get('/pdf/fabrics', [\App\Http\Controllers\FabricPdfController::class, 'print'])
        ->name('pdf.fabrics');

    // 📦 Lista della Spesa - Stampa PDF
    Route::get('/shopping-items/{shoppingItem}/print', [ShoppingItemPrintController::class, 'printSingle'])
        ->name('shopping-items.print.single');

    Route::get('/shopping-items/print/all', [ShoppingItemPrintController::class, 'printAll'])
        ->name('shopping-items.print.all');

    Route::get('/suppliers/{supplier}/shopping-list/print', [SupplierShoppingListController::class, 'print'])
        ->name('suppliers.shopping-list.print');

    // 🗓️ API per calendario consegne
    Route::get('/api/delivery-calendar', [App\Http\Controllers\Api\DeliveryCalendarController::class, 'getDeliveryDates'])
        ->name('api.delivery-calendar');

    // 🗓️ Calendario disponibilità (abiti + aggiusti)
    Route::post('/admin/calendar/availability', [App\Http\Controllers\Admin\CalendarController::class, 'getAvailability'])
        ->name('admin.calendar.availability');
});
