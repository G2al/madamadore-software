<?php

namespace App\Filament\Resources\DressDaTagliareResource\Pages;

use App\Filament\Resources\DressDaTagliareResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDressDaTagliare extends ViewRecord
{
    protected static string $resource = DressDaTagliareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
