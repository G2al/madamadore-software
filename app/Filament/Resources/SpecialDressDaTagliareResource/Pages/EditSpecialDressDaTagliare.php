<?php

namespace App\Filament\Resources\SpecialDressDaTagliareResource\Pages;

use App\Filament\Resources\SpecialDressDaTagliareResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialDressDaTagliare extends EditRecord
{
    protected static string $resource = SpecialDressDaTagliareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
