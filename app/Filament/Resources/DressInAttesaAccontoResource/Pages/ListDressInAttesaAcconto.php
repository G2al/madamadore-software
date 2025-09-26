<?php

namespace App\Filament\Resources\DressInAttesaAccontoResource\Pages;

use App\Filament\Resources\DressInAttesaAccontoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDressInAttesaAcconto extends ListRecords
{
    protected static string $resource = DressInAttesaAccontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
