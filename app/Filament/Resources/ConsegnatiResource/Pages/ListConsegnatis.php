<?php

namespace App\Filament\Resources\ConsegnatiResource\Pages;

use App\Filament\Resources\ConsegnatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsegnatis extends ListRecords
{
    protected static string $resource = ConsegnatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
