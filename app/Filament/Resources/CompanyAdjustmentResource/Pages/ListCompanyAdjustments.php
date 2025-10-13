<?php

namespace App\Filament\Resources\CompanyAdjustmentResource\Pages;

use App\Filament\Resources\CompanyAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\AdjustmentCalendarWidget;

class ListCompanyAdjustments extends ListRecords
{
    protected static string $resource = CompanyAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

        protected function getFooterWidgets(): array
{
    return [
        AdjustmentCalendarWidget::class,
    ];
}
}
