<?php

namespace App\Filament\Resources\CompletatiResource\Pages;

use App\Filament\Resources\CompletatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompletatis extends ListRecords
{
    protected static string $resource = CompletatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
