<?php

namespace App\Filament\Resources\DressConsegnatoResource\Pages;

use App\Filament\Resources\DressConsegnatoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDressConsegnato extends ViewRecord
{
    protected static string $resource = DressConsegnatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
