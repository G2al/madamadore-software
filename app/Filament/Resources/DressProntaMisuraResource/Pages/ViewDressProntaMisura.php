<?php

namespace App\Filament\Resources\DressProntaMisuraResource\Pages;

use App\Filament\Resources\DressProntaMisuraResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDressProntaMisura extends ViewRecord
{
    protected static string $resource = DressProntaMisuraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
