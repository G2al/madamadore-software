<?php

namespace App\Filament\Resources\DressInAttesaAccontoResource\Pages;

use App\Filament\Resources\DressInAttesaAccontoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDressInAttesaAcconto extends ViewRecord
{
    protected static string $resource = DressInAttesaAccontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
