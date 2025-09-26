<?php

namespace App\Filament\Resources\DressProntaMisuraResource\Pages;

use App\Filament\Resources\DressProntaMisuraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDressProntaMisura extends ListRecords
{
    protected static string $resource = DressProntaMisuraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
