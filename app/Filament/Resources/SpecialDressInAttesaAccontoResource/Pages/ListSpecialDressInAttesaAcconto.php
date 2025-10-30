<?php

namespace App\Filament\Resources\SpecialDressInAttesaAccontoResource\Pages;

use App\Filament\Resources\SpecialDressInAttesaAccontoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialDressInAttesaAcconto extends ListRecords
{
    protected static string $resource = SpecialDressInAttesaAccontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
