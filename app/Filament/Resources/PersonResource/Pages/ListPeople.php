<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;               // 👈 importa Actions
use Filament\Resources\Pages\ListRecords;

class ListPeople extends ListRecords
{
    protected static string $resource = PersonResource::class;

    // ✅ bottone "Nuova Persona"
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuova Persona')
                ->icon('heroicon-o-plus'),
        ];
    }

    // ✅ il calendario resta visibile in alto
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PresenceCalendarWidget::class,
        ];
    }
}
