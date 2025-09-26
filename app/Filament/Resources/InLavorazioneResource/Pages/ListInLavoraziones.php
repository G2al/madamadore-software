<?php

namespace App\Filament\Resources\InLavorazioneResource\Pages;

use App\Filament\Resources\InLavorazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInLavoraziones extends ListRecords
{
    protected static string $resource = InLavorazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
