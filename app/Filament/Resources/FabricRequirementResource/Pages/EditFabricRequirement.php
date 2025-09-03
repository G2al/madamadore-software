<?php

namespace App\Filament\Resources\FabricRequirementResource\Pages;

use App\Filament\Resources\FabricRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFabricRequirement extends EditRecord
{
    protected static string $resource = FabricRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
