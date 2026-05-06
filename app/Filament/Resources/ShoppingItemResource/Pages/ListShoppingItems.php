<?php

namespace App\Filament\Resources\ShoppingItemResource\Pages;

use App\Filament\Resources\ShoppingItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShoppingItems extends ListRecords
{
    protected static string $resource = ShoppingItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_unified_shopping_list')
                ->label('Stampa Lista della Spesa Unica')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(route('shopping-items.print.all'))
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\FabricSummaryWidget::class,
        ];
    }
}
