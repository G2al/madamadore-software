<?php

namespace App\Filament\Resources\CompanyInLavorazioneResource\Pages;

use App\Filament\Resources\CompanyInLavorazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyInLavoraziones extends ListRecords
{
    protected static string $resource = CompanyInLavorazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
