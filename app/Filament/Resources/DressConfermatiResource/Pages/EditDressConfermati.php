<?php

namespace App\Filament\Resources\DressConfermatiResource\Pages;

use App\Filament\Resources\DressConfermatiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDressConfermati extends EditRecord
{
    protected static string $resource = DressConfermatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}