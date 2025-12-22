<?php

namespace App\Filament\Widgets;

use App\Models\SpecialDress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpecialDressesOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $row = SpecialDress::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'confermato' THEN 1 ELSE 0 END) as confirmed")
            ->selectRaw("SUM(CASE WHEN status = 'in_lavorazione' THEN 1 ELSE 0 END) as in_lavorazione")
            ->selectRaw("SUM(CASE WHEN status = 'da_tagliare' THEN 1 ELSE 0 END) as da_tagliare")
            ->selectRaw("SUM(CASE WHEN status = 'pronta_misura' THEN 1 ELSE 0 END) as pronta_misura")
            ->selectRaw("SUM(CASE WHEN status = 'consegnato' THEN 1 ELSE 0 END) as consegnato")
            ->first();

        $total         = (int) ($row->total ?? 0);
        $confirmed     = (int) ($row->confirmed ?? 0);
        $inLavorazione = (int) ($row->in_lavorazione ?? 0);
        $daTagliare    = (int) ($row->da_tagliare ?? 0);
        $prontaMisura  = (int) ($row->pronta_misura ?? 0);
        $consegnato    = (int) ($row->consegnato ?? 0);

        return [
            Stat::make('Abiti speciali totali', (string) $total)
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary')
                ->description('Tutti gli abiti speciali registrati'),

            Stat::make('Confermati', (string) $confirmed)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description("{$confirmed} su {$total} confermati"),

            Stat::make('In lavorazione', (string) $inLavorazione)
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning')
                ->description($inLavorazione > 0 ? "{$inLavorazione} in corso" : 'Nessuno in corso'),

            Stat::make('Da tagliare', (string) $daTagliare)
                ->icon('heroicon-o-scissors')
                ->color('info')
                ->description($daTagliare > 0 ? "{$daTagliare} in attesa di taglio" : 'Nessuno'),

            Stat::make('Pronta misura', (string) $prontaMisura)
                ->icon('heroicon-o-chart-bar')
                ->color('secondary')
                ->description($prontaMisura > 0 ? "{$prontaMisura} pronti per misure" : 'Nessuno'),

            Stat::make('Consegnati', (string) $consegnato)
                ->icon('heroicon-o-truck')
                ->color('success')
                ->description("{$consegnato} consegnati"),
        ];
    }
}

