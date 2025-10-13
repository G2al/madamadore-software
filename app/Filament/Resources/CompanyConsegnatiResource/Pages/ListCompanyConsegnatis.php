<?php

namespace App\Filament\Resources\CompanyConsegnatiResource\Pages;

use App\Filament\Resources\CompanyConsegnatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\CompanyAdjustment;

class ListCompanyConsegnatis extends ListRecords
{
    protected static string $resource = CompanyConsegnatiResource::class;

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
                ->badge(fn () => CompanyAdjustment::where('status', 'consegnato')->count())
                ->icon('heroicon-o-rectangle-stack'),

            'ritirati' => Tab::make('Ritirati')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ritirato', true))
                ->badge(fn () => CompanyAdjustment::where('status', 'consegnato')->where('ritirato', true)->count())
                ->icon('heroicon-o-check-circle'),

            'saldati' => Tab::make('Saldati')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('saldato', true))
                ->badge(fn () => CompanyAdjustment::where('status', 'consegnato')->where('saldato', true)->count())
                ->icon('heroicon-o-banknotes'),

            'ritirati_saldati' => Tab::make('Ritirati & Saldati')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ritirato', true)->where('saldato', true))
                ->badge(fn () => CompanyAdjustment::where('status', 'consegnato')->where('ritirato', true)->where('saldato', true)->count())
                ->icon('heroicon-o-check-circle'),
        ];
    }
}