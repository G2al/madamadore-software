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
            'models' => 'sometimes|array', // Supporta array di modelli
            'model' => 'sometimes|string', // Oppure singolo modello (retrocompatibilità)
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
            // Supporta sia 'models' (array) che 'model' (singolo)
            $models = $request->input('models', [$request->input('model')]);
            $dateColumn = $request->input('date_column');
            $month = $request->input('month');
            $year = $request->input('year');

            // Valida che tutti i modelli esistano
            foreach ($models as $model) {
                if (!class_exists($model)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Modello non valido: {$model}"
                    ], 400);
                }
            }

            $availability = $this->queryMultipleModelsAvailability($models, $dateColumn, $month, $year);

            return response()->json([
                'success' => true,
                'data' => $availability,
                'meta' => [
                    'month' => $month,
                    'year' => $year,
                    'models' => $models,
                    'date_column' => $dateColumn,
                    'cached_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Errore query disponibilità calendario', [
                'models' => $request->input('models') ?? $request->input('model'),
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

    /**
     * Query per recuperare disponibilità da MULTIPLI modelli
     * Combina i risultati di Adjustment e CompanyAdjustment
     */
    protected function queryMultipleModelsAvailability(array $models, string $dateColumn, int $month, int $year, ?int $excludeId = null): array
    {
        $modelsKey = implode('_', array_map(fn($m) => class_basename($m), $models));
        $cacheKey = "calendar_availability:{$modelsKey}:{$dateColumn}:{$year}:{$month}:" . ($excludeId ?? 'all');
        
        return Cache::remember($cacheKey, 3600, function () use ($models, $dateColumn, $month, $year, $excludeId) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();

            $availability = [];

            // Itera su ogni modello e combina i risultati
            foreach ($models as $model) {
                $table = (new $model)->getTable();
                
                $query = DB::table($table)
                    ->select(
                        DB::raw("DATE({$table}.{$dateColumn}) as date"),
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereBetween("{$table}.{$dateColumn}", [$startDate, $endDate])
                    ->whereNotNull("{$table}.{$dateColumn}");

                // Se il model è Adjustment o CompanyAdjustment, aggiungi la JOIN per i nomi clienti
                if ($model === 'App\Models\Adjustment' || $model === 'App\Models\CompanyAdjustment') {
                    $query->leftJoin('customers', "{$table}.customer_id", '=', 'customers.id')
                          ->addSelect(DB::raw('GROUP_CONCAT(customers.name SEPARATOR ", ") as customer_names'));
                } 
                // Se il model è Dress, usa customer_name diretto
                elseif ($model === 'App\Models\Dress') {
                    $query->addSelect(DB::raw("GROUP_CONCAT({$table}.customer_name SEPARATOR ', ') as customer_names"));
                }

                if ($excludeId) {
                    $query->where("{$table}.id", '!=', $excludeId);
                }

                $results = $query->groupBy(DB::raw("DATE({$table}.{$dateColumn})"))->get();

                // Combina i risultati nel array $availability
                foreach ($results as $result) {
                    $date = $result->date;
                    
                    if (!isset($availability[$date])) {
                        $availability[$date] = [
                            'count' => 0,
                            'date' => $date,
                            'customers' => []
                        ];
                    }

                    $availability[$date]['count'] += (int) $result->count;

                    // Aggiungi i nomi clienti (evitando duplicati)
                    if (isset($result->customer_names) && $result->customer_names) {
                        $newCustomers = explode(', ', $result->customer_names);
                        $availability[$date]['customers'] = array_unique(
                            array_merge($availability[$date]['customers'], $newCustomers)
                        );
                    }
                }
            }

            return $availability;
        });
    }

    /**
     * Metodo legacy per retrocompatibilità (singolo modello)
     */
    protected function queryAvailabilityData(string $model, string $dateColumn, int $month, int $year, ?int $excludeId = null): array
    {
        return $this->queryMultipleModelsAvailability([$model], $dateColumn, $month, $year, $excludeId);
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