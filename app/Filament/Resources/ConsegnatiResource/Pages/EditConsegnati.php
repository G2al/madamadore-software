<?php

namespace App\Filament\Resources\ConsegnatiResource\Pages;

use App\Filament\Resources\ConsegnatiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsegnati extends EditRecord
{
    protected static string $resource = ConsegnatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
