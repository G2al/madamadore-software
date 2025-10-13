<?php

namespace App\Filament\Resources\CompanyCompletatiResource\Pages;

use App\Filament\Resources\CompanyCompletatiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyCompletati extends EditRecord
{
    protected static string $resource = CompanyCompletatiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
