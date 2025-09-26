<?php

namespace App\Filament\Resources\CompletatiResource\Pages;

use App\Filament\Resources\CompletatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompletati extends ViewRecord
{
    protected static string $resource = CompletatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
