<?php

namespace App\Filament\Widgets;

use App\Models\Dress;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class DressesCalendar extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Consegne';

    // Deve avere la stessa firma del parent:
    public Model|string|null $model = Dress::class;

    /**
     * Eventi mostrati nel calendario.
     *
     * @param  array  $fetchInfo  // contiene start/end visibili ecc. (se ti serve filtrare)
     * @return array<int, EventData>
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return Dress::query()
            ->whereNotNull('delivery_date')
            ->get()
            ->map(fn (Dress $dress) => EventData::make()
                ->id((string) $dress->getKey())
                ->title($dress->customer_name)
                ->start($dress->delivery_date->toDateString())
                ->end($dress->delivery_date->toDateString()) // evento di 1 giorno
                ->backgroundColor('#6366F1')
                ->textColor('#ffffff')
            )
            ->toArray();
    }
}
