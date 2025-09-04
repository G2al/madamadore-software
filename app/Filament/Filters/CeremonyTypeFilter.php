<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;

class CeremonyTypeFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('ceremony_type')
            ->label('Tipo Cerimonia')
            ->options(self::getCeremonyTypes());
    }

    protected static function getCeremonyTypes(): array
    {
        return config('dress.ceremonies', []);
    }
}
