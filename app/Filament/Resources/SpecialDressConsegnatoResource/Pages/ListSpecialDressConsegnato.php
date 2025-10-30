<?php

namespace App\Filament\Resources\SpecialDressConsegnatoResource\Pages;

use App\Filament\Resources\SpecialDressConsegnatoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialDressConsegnato extends ListRecords
{
    protected static string $resource = SpecialDressConsegnatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
