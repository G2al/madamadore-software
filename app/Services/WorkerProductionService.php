<?php

namespace App\Services;

use App\Models\AdjustmentItem;
use App\Models\CompanyAdjustmentItem;
use App\Models\Worker;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class WorkerProductionService
{
    public function totalsForDate(Worker $worker, CarbonInterface|string|null $date = null): array
    {
        $day = $date instanceof CarbonInterface
            ? Carbon::instance($date)->toDateString()
            : Carbon::parse($date ?? now())->toDateString();

        $normalQuery = AdjustmentItem::query()
            ->where('worker_id', $worker->id)
            ->whereDate('completed_at', $day);

        $companyQuery = CompanyAdjustmentItem::query()
            ->where('worker_id', $worker->id)
            ->whereDate('completed_at', $day);

        return [
            'work_count' => (clone $normalQuery)->count() + (clone $companyQuery)->count(),
            'total_amount' => (float) (clone $normalQuery)->sum('price') + (float) (clone $companyQuery)->sum('price'),
        ];
    }
}
