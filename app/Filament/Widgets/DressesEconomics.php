<?php

namespace App\Filament\Widgets;

use App\Models\Dress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DressesEconomics extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $row = Dress::query()
            ->selectRaw('COALESCE(SUM(total_purchase_cost),0) as total_purchase')
            ->selectRaw('COALESCE(SUM(total_client_price),0) as total_client')
            ->first();

        $totalPurchase = (float) $row->total_purchase;
        $totalClient   = (float) $row->total_client;
        $profit        = $totalClient - $totalPurchase;

        // 🆕 Calcolo incassato (abiti saldati)
        $incassato = Dress::query()
            ->where('saldato', true)
            ->sum('total_client_price');

        // 🆕 Calcolo da incassare (abiti NON saldati)
        $daIncassare = Dress::query()
            ->where('saldato', false)
            ->sum('total_client_price');

        return [
            Stat::make('Costo Totale (per te)', '€ ' . number_format($totalPurchase, 2, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('danger')
                ->description('Spese materiali & manifattura'),

            Stat::make('Prezzo Totale (clienti)', '€ ' . number_format($totalClient, 2, ',', '.'))
                ->icon('heroicon-o-currency-euro')
                ->color('info')
                ->description('Entrate complessive dai clienti'),

            Stat::make('Profitto', '€ ' . number_format($profit, 2, ',', '.'))
                ->icon('heroicon-o-chart-bar')
                ->color($profit >= 0 ? 'success' : 'danger')
                ->description($profit >= 0 ? 'Utile netto' : 'Perdita'),

            // 🆕 REALMENTE INCASSATO
            Stat::make('Incassato', '€ ' . number_format($incassato, 2, ',', '.'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description('Abiti completamente saldati'),

            // 🆕 DA INCASSARE
            Stat::make('Da Incassare', '€ ' . number_format($daIncassare, 2, ',', '.'))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->description('Abiti non ancora saldati'),
        ];
    }
}