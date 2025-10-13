<?php


namespace App\Filament\Widgets;

use App\Models\Adjustment;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AdjustmentCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Consegne Aggiusti';
    protected static ?int $sort = 3;

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
        ->map(function (Adjustment $adjustment) {
            // Colori in base allo stato
            $statusColors = [
                'in_lavorazione' => '#a98a0cff', // giallo
                'confermato'     => '#3b82f6', // blu
                'completato'     => '#3b82f6', // blu
                'consegnato'     => '#22c55e', // verde
                'default'        => '#9ca3af', // grigio
            ];

            // Scegli colore in base allo stato
            $color = $statusColors[$adjustment->status] ?? $statusColors['default'];

            return EventData::make()
                ->id((string) $adjustment->getKey())
                ->title(($adjustment->customer?->name ?? 'Cliente sconosciuto') . ' (' . ucfirst(str_replace('_', ' ', $adjustment->status)) . ')')
                ->start($adjustment->delivery_date->toDateString())
                ->end($adjustment->delivery_date->toDateString())
                ->backgroundColor($color)
                ->textColor('#ffffff')
                ->url(route('filament.admin.resources.adjustments.edit', $adjustment));
        })
        ->toArray();
}

}
