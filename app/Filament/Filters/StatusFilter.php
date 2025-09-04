<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\DressResource;

class StatusFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Stato')
            ->options(DressResource::getStatusLabels());
    }
}
