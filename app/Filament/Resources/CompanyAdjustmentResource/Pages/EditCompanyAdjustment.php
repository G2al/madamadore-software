<?php

namespace App\Filament\Resources\CompanyAdjustmentResource\Pages;

use App\Filament\Resources\CompanyAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyAdjustment extends EditRecord
{
    protected static string $resource = CompanyAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
