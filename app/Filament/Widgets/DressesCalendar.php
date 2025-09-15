<?php

namespace App\Filament\Widgets;

use App\Models\Dress;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class DressesCalendar extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Consegne';
    protected static ?int $sort = 4;

    // Rimuovi questa riga per eliminare il bottone Nuovo
    // public Model|string|null $model = Dress::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Dress::query()
            ->whereNotNull('delivery_date')
            ->get()
            ->map(fn (Dress $dress) => EventData::make()
                ->id((string) $dress->getKey())
                ->title($dress->customer_name)
                ->start($dress->delivery_date->toDateString())
                ->end($dress->delivery_date->toDateString())
                ->backgroundColor('#6366F1')
                ->textColor('#ffffff')
                ->url(route('filament.admin.resources.dresses.edit', $dress))
            )
            ->toArray();
    }
}