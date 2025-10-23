<?php

namespace App\Filament\Widgets;

use App\Models\Adjustment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdjustmentsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $row = Adjustment::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'in_lavorazione' THEN 1 ELSE 0 END) as in_lavorazione")
            ->selectRaw("SUM(CASE WHEN status = 'completato' THEN 1 ELSE 0 END) as completato")
            ->first();

        $total         = (int) ($row->total ?? 0);
        $inLavorazione = (int) ($row->in_lavorazione ?? 0);
        $completato    = (int) ($row->completato ?? 0);

        return [
            Stat::make('Aggiusti totali', (string) $total)
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary')
                ->description('Tutti gli aggiusti registrati'),

            Stat::make('In lavorazione', (string) $inLavorazione)
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning')
                ->description($inLavorazione > 0 ? "{$inLavorazione} in corso" : 'Nessuno in corso'),

            Stat::make('Completati', (string) $completato)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description("{$completato} su {$total} completati"),
        ];
    }
}