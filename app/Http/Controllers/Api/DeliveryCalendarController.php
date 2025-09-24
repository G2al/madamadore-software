<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dress;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeliveryCalendarController extends Controller
{
    /**
     * Ritorna il conteggio di abiti per ogni data di consegna
     * per colorare il calendario DatePicker
     */
    public function getDeliveryDates(Request $request)
    {
        $startDate = $request->get('start', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end', now()->endOfMonth()->addMonths(2)->toDateString());
        $excludeDressId = $request->get('exclude');

        $dresses = Dress::query()
            ->whereNotNull('delivery_date')
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->when($excludeDressId, fn($q) => $q->where('id', '!=', $excludeDressId))
            ->get(['delivery_date', 'customer_name', 'ceremony_type']);

        // Raggruppa per data e conta
        $datesSummary = $dresses->groupBy('delivery_date')->map(function ($dressesOnDate, $date) {
            $count = $dressesOnDate->count();

            // Determina colore basato su carico
            if ($count === 0) {
                $color = 'green';
                $intensity = 'light';
            } elseif ($count <= 2) {
                $color = 'yellow';
                $intensity = 'light';
            } elseif ($count <= 4) {
                $color = 'orange';
                $intensity = 'medium';
            } else {
                $color = 'red';
                $intensity = 'strong';
            }

            return [
                'date' => $date,
                'count' => $count,
                'color' => $color,
                'intensity' => $intensity,
                'customers' => $dressesOnDate->pluck('customer_name')->take(3)->toArray()
            ];
        });

        return response()->json($datesSummary->values());
    }
}
