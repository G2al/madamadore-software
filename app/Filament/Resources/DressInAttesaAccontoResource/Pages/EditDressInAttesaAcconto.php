<?php

namespace App\Filament\Resources\DressInAttesaAccontoResource\Pages;

use App\Filament\Resources\DressInAttesaAccontoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDressInAttesaAcconto extends EditRecord
{
    protected static string $resource = DressInAttesaAccontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
