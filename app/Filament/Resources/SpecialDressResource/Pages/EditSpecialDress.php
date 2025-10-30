<?php

namespace App\Filament\Resources\SpecialDressResource\Pages;

use App\Filament\Resources\SpecialDressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialDress extends EditRecord
{
    protected static string $resource = SpecialDressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
