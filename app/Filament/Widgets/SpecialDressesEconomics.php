<?php

namespace App\Filament\Widgets;

use App\Models\SpecialDress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class SpecialDressesEconomics extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $row = SpecialDress::query()
            ->selectRaw('COALESCE(SUM(total_client_price),0) as total_client')
            ->selectRaw('COALESCE(SUM(deposit),0) as total_deposit')
            ->selectRaw('COALESCE(SUM(remaining),0) as total_remaining')
            ->first();

        $totalClient    = (float) $row->total_client;
        $totalDeposit   = (float) $row->total_deposit;
        $totalRemaining = (float) $row->total_remaining;

        // usa 1/0 per i booleani
        $incassato   = (float) SpecialDress::query()->where('saldato', 1)->sum('total_client_price');
        $daIncassare = (float) SpecialDress::query()->where('saldato', 0)->sum('total_client_price');

        return [
            Stat::make('Prezzo Totale (clienti)', $this->maskedValue($totalClient))
                ->icon('heroicon-o-currency-euro')
                ->color('info')
                ->description('Entrate complessive dai clienti'),

            Stat::make('Acconti Totali', $this->maskedValue($totalDeposit))
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->description('Acconti incassati'),

            Stat::make('Rimanente Totale', $this->maskedValue($totalRemaining))
                ->icon('heroicon-o-chart-bar')
                ->color('warning')
                ->description('Totale ancora da incassare'),

            Stat::make('Incassato', $this->maskedValue($incassato))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description('Abiti speciali completamente saldati'),

            Stat::make('Da Incassare', $this->maskedValue($daIncassare))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->description('Abiti speciali non ancora saldati'),
        ];
    }

private function maskedValue(float $value): HtmlString
{
    $formatted = '&euro; ' . number_format($value, 2, ',', '.');

    $html = <<<HTML
<div x-data="{ show: false }" class="flex items-center gap-2">
    <span x-show="show" x-transition.opacity.duration.200ms>{$formatted}</span>
    <span x-show="!show" x-transition.opacity.duration.200ms class="select-none blur-sm">&euro; &bull;&bull;&bull;&bull;&bull;</span>
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

