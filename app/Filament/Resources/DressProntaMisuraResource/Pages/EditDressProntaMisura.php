<?php

namespace App\Filament\Resources\DressProntaMisuraResource\Pages;

use App\Filament\Resources\DressProntaMisuraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDressProntaMisura extends EditRecord
{
    protected static string $resource = DressProntaMisuraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
