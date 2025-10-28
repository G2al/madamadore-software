<?php

namespace App\Filament\Resources\AppuntamentoResource\Pages;

use App\Filament\Resources\AppuntamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppuntamento extends EditRecord
{
    protected static string $resource = AppuntamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
