<?php

namespace App\Filament\Widgets;

use App\Models\Adjustment;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AdjustmentCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Consegne Aggiusti';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Adjustment::query()
            ->with('customer')
            ->whereNotNull('delivery_date')
            ->get()
            ->map(fn (Adjustment $adjustment) => EventData::make()
                ->id((string) $adjustment->getKey())
                ->title($adjustment->customer?->name ?? 'Cliente sconosciuto')
                ->start($adjustment->delivery_date->toDateString())
                ->end($adjustment->delivery_date->toDateString())
                ->backgroundColor('#6366F1')
                ->textColor('#ffffff')
                ->url(route('filament.admin.resources.adjustments.edit', $adjustment))
            )
            ->toArray();
    }
}