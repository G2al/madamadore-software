<?php

namespace App\Filament\Resources\CompanyConsegnatiResource\Pages;

use App\Filament\Resources\CompanyConsegnatiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyConsegnati extends EditRecord
{
    protected static string $resource = CompanyConsegnatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
