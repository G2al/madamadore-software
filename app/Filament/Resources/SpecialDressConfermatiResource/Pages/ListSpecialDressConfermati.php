<?php

namespace App\Filament\Resources\SpecialDressConfermatiResource\Pages;

use App\Filament\Resources\SpecialDressConfermatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialDressConfermati extends ListRecords
{
    protected static string $resource = SpecialDressConfermatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
