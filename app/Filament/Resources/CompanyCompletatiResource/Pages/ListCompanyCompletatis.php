<?php

namespace App\Filament\Resources\CompanyCompletatiResource\Pages;

use App\Filament\Resources\CompanyCompletatiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyCompletatis extends ListRecords
{
    protected static string $resource = CompanyCompletatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
