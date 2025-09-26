<?php

namespace App\Filament\Resources\InLavorazioneResource\Pages;

use App\Filament\Resources\InLavorazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInLavorazione extends ViewRecord
{
    protected static string $resource = InLavorazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
