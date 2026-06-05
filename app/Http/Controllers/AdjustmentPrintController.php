<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\CompanyAdjustment;
use App\Services\PdfResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;

class AdjustmentPrintController extends Controller
{
    public function dailyAdjustments(string $date)
    {
        $day = Carbon::parse($date)->startOfDay()->locale('it');

        return $this->printPeriod(
            Adjustment::query(),
            'Aggiusti',
            'giorno',
            $day,
            $day,
            'aggiusti-giorno-' . $day->format('Y-m-d') . '.pdf'
        );
    }

    public function weeklyAdjustments(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay()->locale('it');
        $end = Carbon::parse($endDate)->startOfDay()->locale('it');

        return $this->printPeriod(
            Adjustment::query(),
            'Aggiusti',
            'settimana',
            $start,
            $end,
            'aggiusti-settimana-' . $start->format('Y-m-d') . '-' . $end->format('Y-m-d') . '.pdf'
        );
    }

    public function dailyCompanyAdjustments(string $date)
    {
        $day = Carbon::parse($date)->startOfDay()->locale('it');

        return $this->printPeriod(
            CompanyAdjustment::query(),
            'Aggiusti Aziende',
            'giorno',
            $day,
            $day,
            'aggiusti-aziende-giorno-' . $day->format('Y-m-d') . '.pdf'
        );
    }

    public function weeklyCompanyAdjustments(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay()->locale('it');
        $end = Carbon::parse($endDate)->startOfDay()->locale('it');

        return $this->printPeriod(
            CompanyAdjustment::query(),
            'Aggiusti Aziende',
            'settimana',
            $start,
            $end,
            'aggiusti-aziende-settimana-' . $start->format('Y-m-d') . '-' . $end->format('Y-m-d') . '.pdf'
        );
    }

    private function printPeriod(
        Builder $query,
        string $title,
        string $periodType,
        Carbon $start,
        Carbon $end,
        string $filename
    ) {
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $modelClass = get_class($query->getModel());

        $records = $query
            ->with(['customer', 'items.worker', 'primaryWorker'])
            ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('delivery_date')
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('pdf.adjustments-period', [
            'records' => $records,
            'title' => $title,
            'periodType' => $periodType,
            'startDate' => $start,
            'endDate' => $end,
            'statusLabels' => $modelClass::getStatusLabels(),
        ])->setPaper('a4', 'portrait');

        return PdfResponseService::streamPdf($pdf, $filename);
    }
}
