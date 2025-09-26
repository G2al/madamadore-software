<?php

namespace App\Filament\Resources\DressDaTagliareResource\Pages;

use App\Filament\Resources\DressDaTagliareResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDressDaTagliare extends ListRecords
{
    protected static string $resource = DressDaTagliareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
