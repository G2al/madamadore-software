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
            ->selectRaw('COALESCE(SUM(deposit),0) as total_deposit')
            ->first();

        $totalClient = (float) $row->total_client;
        $totalProfit = (float) $row->total_profit;
        $totalDeposit = (float) $row->total_deposit;

        return [
            Stat::make('Prezzo Totale (clienti)', '€ ' . number_format($totalClient, 2, ',', '.'))
                ->icon('heroicon-o-currency-euro')
                ->color('info')
                ->description('Entrate complessive dai clienti'),

            Stat::make('Profitto', '€ ' . number_format($totalProfit, 2, ',', '.'))
                ->icon('heroicon-o-chart-bar')
                ->color($totalProfit >= 0 ? 'success' : 'danger')
                ->description($totalProfit >= 0 ? 'Utile netto' : 'Perdita'),

            Stat::make('Acconti Incassati', '€ ' . number_format($totalDeposit, 2, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->description('Totale acconti ricevuti'),
        ];
    }
}