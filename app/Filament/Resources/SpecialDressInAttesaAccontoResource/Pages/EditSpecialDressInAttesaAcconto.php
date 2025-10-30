<?php

namespace App\Filament\Resources\SpecialDressInAttesaAccontoResource\Pages;

use App\Filament\Resources\SpecialDressInAttesaAccontoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialDressInAttesaAcconto extends EditRecord
{
    protected static string $resource = SpecialDressInAttesaAccontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
