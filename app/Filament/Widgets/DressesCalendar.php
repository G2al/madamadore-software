<?php

namespace App\Filament\Widgets;

use App\Models\Dress;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class DressesCalendar extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario Consegne';
    protected static ?int $sort = 3;

        public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }


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
        ->map(function (Dress $dress) {
            // 🎨 Colori per stato (coerenti con i tag Filament)
            $statusColors = [
                'in_attesa_acconto' => '#f59e0b', // arancione (warning)
                'confermato'        => '#3b82f6', // blu (info)
                'in_lavorazione'    => '#6366f1', // indaco (primary)
                'da_tagliare'       => '#0ea5e9', // azzurro (primary soft)
                'pronta_misura'     => '#a855f7', // viola (secondary)
                'consegnato'        => '#22c55e', // verde (success)
                'default'           => '#9ca3af', // grigio (neutro)
            ];

            // 🎯 Scegli il colore in base allo stato
            $color = $statusColors[$dress->status] ?? $statusColors['default'];

            // 📅 Crea evento nel calendario
            return EventData::make()
                ->id((string) $dress->getKey())
                ->title($dress->customer_name . ' (' . ucfirst(str_replace('_', ' ', $dress->status)) . ')')
                ->start($dress->delivery_date->toDateString())
                ->end($dress->delivery_date->toDateString())
                ->backgroundColor($color)
                ->textColor('#ffffff')
                ->url(route('filament.admin.resources.dresses.edit', $dress));
        })
        ->toArray();
}

}