<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function getAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'model' => 'required|string',
            'date_column' => 'required|string',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2020,2030',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $model = $request->input('model');
            $dateColumn = $request->input('date_column');
            $month = $request->input('month');
            $year = $request->input('year');

            // Valida che il modello esista
            if (!class_exists($model)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modello non valido'
                ], 400);
            }

            $availability = $this->queryAvailabilityData($model, $dateColumn, $month, $year);

            return response()->json([
                'success' => true,
                'data' => $availability,
                'meta' => [
                    'month' => $month,
                    'year' => $year,
                    'model' => $model,
                    'date_column' => $dateColumn,
                    'cached_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Errore query disponibilità calendario', [
                'model' => $request->input('model'),
                'date_column' => $request->input('date_column'), 
                'month' => $request->input('month'),
                'year' => $request->input('year'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nel caricamento dati disponibilità'
            ], 500);
        }
    }

    protected function queryAvailabilityData(string $model, string $dateColumn, int $month, int $year): array
    {
        $cacheKey = "calendar_availability:{$model}:{$dateColumn}:{$year}:{$month}";
        
        return Cache::remember($cacheKey, 3600, function () use ($model, $dateColumn, $month, $year) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            // Query che include anche i nomi clienti
            $results = DB::table((new $model)->getTable())
                ->select(
                    DB::raw("DATE({$dateColumn}) as date"),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('GROUP_CONCAT(customer_name SEPARATOR ", ") as customer_names')
                )
                ->whereBetween($dateColumn, [$startDate, $endDate])
                ->whereNotNull($dateColumn)
                ->groupBy(DB::raw("DATE({$dateColumn})"))
                ->get();

            $availability = [];
            foreach ($results as $result) {
                $availability[$result->date] = [
                    'count' => (int) $result->count,
                    'date' => $result->date,
                    'customers' => $result->customer_names ? explode(', ', $result->customer_names) : []
                ];
            }

            return $availability;
        });
    }

    /**
     * Pulisce la cache di disponibilità per un modello/data specifica
     * Utile quando vengono creati/aggiornati/eliminati record
     */
    public function clearAvailabilityCache(string $model, string $dateColumn, Carbon $date): void
    {
        $month = $date->month;
        $year = $date->year;
        $cacheKey = "calendar_availability:{$model}:{$dateColumn}:{$year}:{$month}";
        
        Cache::forget($cacheKey);
    }
}