<?php

namespace App\Filament\Resources\SpecialDressResource\Pages;

use App\Filament\Resources\SpecialDressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialDresses extends ListRecords
{
    protected static string $resource = SpecialDressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
