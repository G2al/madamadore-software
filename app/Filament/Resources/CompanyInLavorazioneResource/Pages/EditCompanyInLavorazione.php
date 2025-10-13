<?php

namespace App\Filament\Resources\CompanyInLavorazioneResource\Pages;

use App\Filament\Resources\CompanyInLavorazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyInLavorazione extends EditRecord
{
    protected static string $resource = CompanyInLavorazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
