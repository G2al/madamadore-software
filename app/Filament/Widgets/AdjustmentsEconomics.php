<?php

namespace App\Filament\Widgets;

use App\Models\Adjustment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdjustmentsEconomics extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 2;
    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $row = Adjustment::query()
            ->selectRaw('COALESCE(SUM(client_price),0) as total_client')
            ->selectRaw('COALESCE(SUM(profit),0) as total_profit')
            ->selectRaw('COALESCE(SUM(CASE WHEN saldato = 1 THEN client_price ELSE 0 END),0) as total_saldato')
            ->selectRaw('COALESCE(SUM(remaining),0) as total_remaining')
            ->first();

        $totalClient = (float) $row->total_client;
        $totalProfit = (float) $row->total_profit;
        $totalSaldato = (float) $row->total_saldato;
        $totalRemaining = (float) $row->total_remaining;

        return [
            Stat::make('Prezzo Totale', '€ ' . number_format($totalClient, 2, ',', '.'))
                ->icon('heroicon-o-currency-euro')
                ->color('info')
                ->description('Entrate totali clienti'),

            Stat::make('Profitto', '€ ' . number_format($totalProfit, 2, ',', '.'))
                ->icon('heroicon-o-chart-bar')
                ->color($totalProfit >= 0 ? 'success' : 'danger')
                ->description($totalProfit >= 0 ? 'Utile netto' : 'Perdita'),

            Stat::make('Saldati', '€ ' . number_format($totalSaldato, 2, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->description('Pagamenti completati'),

            Stat::make('Da Incassare', '€ ' . number_format($totalRemaining, 2, ',', '.'))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->description('Importi ancora da saldare'),
        ];
    }
}