<?php

namespace App\Filament\Resources\AdjustmentResource\Pages;

use App\Filament\Resources\AdjustmentResource;
use App\Filament\Widgets\AdjustmentsOverview;
use App\Filament\Widgets\AdjustmentsEconomics;
use App\Filament\Widgets\AdjustmentCalendarWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdjustments extends ListRecords
{
    protected static string $resource = AdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // Widget SOPRA la tabella (Overview + Economics)
    protected function getHeaderWidgets(): array
    {
        return [
            AdjustmentsOverview::class,
            AdjustmentsEconomics::class,
        ];
    }

    // Widget SOTTO la tabella (Calendario)
    protected function getFooterWidgets(): array
    {
        return [
            AdjustmentCalendarWidget::class,
        ];
    }
}