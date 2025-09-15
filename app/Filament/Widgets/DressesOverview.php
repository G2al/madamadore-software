<?php

namespace App\Filament\Widgets;

use App\Models\Dress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DressesOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $row = Dress::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'confermato' THEN 1 ELSE 0 END) as confirmed")
            ->selectRaw("SUM(CASE WHEN status = 'in_lavorazione' THEN 1 ELSE 0 END) as in_lavorazione")
            ->first();

        $total         = (int) ($row->total ?? 0);
        $confirmed     = (int) ($row->confirmed ?? 0);
        $inLavorazione = (int) ($row->in_lavorazione ?? 0);

        return [
            Stat::make('Abiti totali', (string) $total)
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary')
                ->description('Tutti gli abiti registrati'),

            Stat::make('Confermati', (string) $confirmed)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description("{$confirmed} su {$total} confermati"),

            Stat::make('In lavorazione', (string) $inLavorazione)
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning')
                ->description($inLavorazione > 0 ? "{$inLavorazione} in corso" : 'Nessuno in corso'),
        ];
    }
}
