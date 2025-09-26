<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Filament\Widgets\StudentCalendarWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;  

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

        // ✅ bottone "Nuova Persona"
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuova Persona')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StudentCalendarWidget::class,
        ];
    }
}
