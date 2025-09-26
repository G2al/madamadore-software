<?php

namespace App\Filament\Resources\DressConfermatiResource\Pages;

use App\Filament\Resources\DressConfermatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDressConfermati extends ViewRecord
{
    protected static string $resource = DressConfermatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}