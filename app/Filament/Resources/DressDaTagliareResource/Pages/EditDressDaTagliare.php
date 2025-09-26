<?php

namespace App\Filament\Resources\DressDaTagliareResource\Pages;

use App\Filament\Resources\DressDaTagliareResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDressDaTagliare extends EditRecord
{
    protected static string $resource = DressDaTagliareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
