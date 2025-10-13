<?php

use Illuminate\Support\Facades\Route;
use App\Services\AdjustmentReceiptService;
use App\Models\Adjustment;
use App\Models\CompanyAdjustment;

// Redirect alla dashboard admin
Route::get('/', function () {
    return redirect('/admin');
});

// Route per la ricevuta aggiusti normali (PDF inline)
Route::get('/adjustments/{adjustment}/receipt', function (Adjustment $adjustment, AdjustmentReceiptService $service) {
    $pdf = $service->generateThermalReceipt($adjustment);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="ricevuta-aggiusto-' . $adjustment->id . '.pdf"',
    ]);
})->name('adjustments.receipt');

// Route per la ricevuta aggiusti aziendali (PDF inline)
Route::get('/company-adjustments/{companyAdjustment}/receipt', function (CompanyAdjustment $companyAdjustment, AdjustmentReceiptService $service) {
    $pdf = $service->generateThermalReceiptCompany($companyAdjustment);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="ricevuta-aggiusto-aziendale-' . $companyAdjustment->id . '.pdf"',
    ]);
})->name('company-adjustments.receipt');

// Nuove route per i PDF degli abiti
Route::middleware(['auth'])->group(function () {
    Route::get('/pdf/modellino/{dress}', [App\Http\Controllers\PdfController::class, 'modellino'])->name('pdf.modellino');
    Route::get('/pdf/preventivo/{dress}', [App\Http\Controllers\PdfController::class, 'preventivo'])->name('pdf.preventivo');

    // API per calendario consegne
    Route::get('/api/delivery-calendar', [App\Http\Controllers\Api\DeliveryCalendarController::class, 'getDeliveryDates'])->name('api.delivery-calendar');
    
    // Route per calendario disponibilità (aggiusti + abiti)
    Route::post('/admin/calendar/availability', [App\Http\Controllers\Admin\CalendarController::class, 'getAvailability'])->name('admin.calendar.availability');
});