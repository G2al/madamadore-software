<?php

namespace App\Filament\Resources\SpecialDressProntaMisuraResource\Pages;

use App\Filament\Resources\SpecialDressProntaMisuraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialDressProntaMisura extends ListRecords
{
    protected static string $resource = SpecialDressProntaMisuraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
