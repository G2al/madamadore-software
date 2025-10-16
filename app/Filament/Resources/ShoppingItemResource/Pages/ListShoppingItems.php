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
            Actions\CreateAction::make(),
        ];
    }
}
