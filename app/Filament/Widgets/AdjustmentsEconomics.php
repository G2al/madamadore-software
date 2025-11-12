<?php

namespace App\Filament\Widgets;

use App\Models\Adjustment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class AdjustmentsEconomics extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';
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

        $totalClient    = (float) $row->total_client;
        $totalProfit    = (float) $row->total_profit;
        $totalSaldato   = (float) $row->total_saldato;
        $totalRemaining = (float) $row->total_remaining;

        return [
            Stat::make('Prezzo Totale', $this->maskedValue($totalClient))
                ->icon('heroicon-o-currency-euro')
                ->color('info')
                ->description('Entrate totali clienti'),

            Stat::make('Profitto', $this->maskedValue($totalProfit))
                ->icon('heroicon-o-chart-bar')
                ->color($totalProfit >= 0 ? 'success' : 'danger')
                ->description($totalProfit >= 0 ? 'Utile netto' : 'Perdita'),

            Stat::make('Saldati', $this->maskedValue($totalSaldato))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->description('Pagamenti completati'),

            Stat::make('Da Incassare', $this->maskedValue($totalRemaining))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->description('Importi ancora da saldare'),
        ];
    }

    private function maskedValue(float $value): HtmlString
    {
        $formatted = '€ ' . number_format($value, 2, ',', '.');

        $html = <<<HTML
        <div x-data="{ show: false }" class="flex items-center gap-2">
            <span x-show="show" x-transition.opacity.duration.200ms>{$formatted}</span>
            <span x-show="!show" x-transition.opacity.duration.200ms class="select-none blur-sm">€ •••••</span>
            <button @click="show = !show" type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path x-show="!show" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path x-show="!show" stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <path x-show="show" stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.974 9.974 0 012.016-3.286m3.134-2.205A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.975 9.975 0 01-2.331 3.994M3 3l18 18" />
                </svg>
            </button>
        </div>
        HTML;

        return new HtmlString($html);
    }
}
