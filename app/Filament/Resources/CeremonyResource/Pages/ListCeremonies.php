<?php

namespace App\Filament\Resources\CeremonyResource\Pages;

use App\Filament\Resources\CeremonyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCeremonies extends ListRecords
{
    protected static string $resource = CeremonyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
