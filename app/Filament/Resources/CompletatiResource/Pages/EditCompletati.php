<?php

namespace App\Filament\Resources\CompletatiResource\Pages;

use App\Filament\Resources\CompletatiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompletati extends EditRecord
{
    protected static string $resource = CompletatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
