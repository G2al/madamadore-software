<?php

namespace App\Filament\Resources\DressConsegnatoResource\Pages;

use App\Filament\Resources\DressConsegnatoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDressConsegnato extends EditRecord
{
    protected static string $resource = DressConsegnatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
