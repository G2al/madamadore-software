<?php

namespace App\Filament\Resources\SpecialDressConfermatiResource\Pages;

use App\Filament\Resources\SpecialDressConfermatiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialDressConfermati extends EditRecord
{
    protected static string $resource = SpecialDressConfermatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
