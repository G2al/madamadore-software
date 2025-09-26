<?php

namespace App\Filament\Resources\DressConfermatiResource\Pages;

use App\Filament\Resources\DressConfermatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDressConfermati extends ListRecords
{
    protected static string $resource = DressConfermatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}