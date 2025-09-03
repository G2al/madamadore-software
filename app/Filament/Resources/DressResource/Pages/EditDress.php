<?php

namespace App\Filament\Resources\DressResource\Pages;

use App\Filament\Resources\DressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDress extends EditRecord
{
    protected static string $resource = DressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
