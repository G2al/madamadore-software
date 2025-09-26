<?php

namespace App\Filament\Resources\DressConsegnatoResource\Pages;

use App\Filament\Resources\DressConsegnatoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDressConsegnato extends ListRecords
{
    protected static string $resource = DressConsegnatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
