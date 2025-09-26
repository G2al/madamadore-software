<?php

namespace App\Filament\Resources\ConsegnatiResource\Pages;

use App\Filament\Resources\ConsegnatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewConsegnati extends ViewRecord
{
    protected static string $resource = ConsegnatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
