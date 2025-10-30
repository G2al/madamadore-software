<?php

namespace App\Filament\Resources\SpecialDressProntaMisuraResource\Pages;

use App\Filament\Resources\SpecialDressProntaMisuraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialDressProntaMisura extends EditRecord
{
    protected static string $resource = SpecialDressProntaMisuraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
