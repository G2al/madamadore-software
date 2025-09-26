<?php

namespace App\Filament\Resources\InLavorazioneResource\Pages;

use App\Filament\Resources\InLavorazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInLavorazione extends EditRecord
{
    protected static string $resource = InLavorazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
