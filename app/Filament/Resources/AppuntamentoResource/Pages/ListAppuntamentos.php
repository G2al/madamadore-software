<?php

namespace App\Filament\Resources\AppuntamentoResource\Pages;

use App\Filament\Resources\AppuntamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppuntamentos extends ListRecords
{
    protected static string $resource = AppuntamentoResource::class;

    // ✅ Solo le azioni “pulsante” qui
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // ✅ I widget (come il calendario) vanno qui
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\AppuntamentoResource\Widgets\AppuntamentiCalendarWidget::class,
        ];
    }
}
