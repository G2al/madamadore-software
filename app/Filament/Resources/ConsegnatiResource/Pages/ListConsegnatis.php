<?php

namespace App\Filament\Resources\ConsegnatiResource\Pages;

use App\Filament\Resources\ConsegnatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Adjustment;

class ListConsegnatis extends ListRecords
{
    protected static string $resource = ConsegnatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
{
    return [
        'tutti' => Tab::make('Tutti')
            ->badge(fn () => Adjustment::where('status', 'consegnato')->count())
            ->icon('heroicon-o-rectangle-stack'),

        'ritirati' => Tab::make('Ritirati')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('ritirato', true))
            ->badge(fn () => Adjustment::where('status', 'consegnato')->where('ritirato', true)->count())
            ->icon('heroicon-o-check-circle'),

        'saldati' => Tab::make('Saldati')
            // uso il toggle booleano "saldato" per coerenza con la tua UI
            ->modifyQueryUsing(fn (Builder $query) => $query->where('saldato', true))
            ->badge(fn () => Adjustment::where('status', 'consegnato')->where('saldato', true)->count())
            ->icon('heroicon-o-banknotes'),

        'ritirati_saldati' => Tab::make('Ritirati & Saldati')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('ritirato', true)->where('saldato', true))
            ->badge(fn () => Adjustment::where('status', 'consegnato')->where('ritirato', true)->where('saldato', true)->count())
            ->icon('heroicon-o-check-circle'),
    ];
}

}
