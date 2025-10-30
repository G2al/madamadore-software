<?php

namespace App\Filament\Resources\SpecialDressConsegnatoResource\Pages;

use App\Filament\Resources\SpecialDressConsegnatoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialDressConsegnato extends EditRecord
{
    protected static string $resource = SpecialDressConsegnatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
