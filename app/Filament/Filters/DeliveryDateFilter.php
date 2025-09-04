<?php

namespace App\Filament\Filters;

use Filament\Forms;
use Filament\Tables;

class DeliveryDateFilter
{
    public static function make(string $column = 'delivery_date'): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make($column)
            ->form([
                Forms\Components\DatePicker::make('from')->label('Consegna da'),
                Forms\Components\DatePicker::make('until')->label('Consegna fino a'),
            ])
            ->query(function ($query, array $data) use ($column) {
                return $query
                    ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate($column, '>=', $date))
                    ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate($column, '<=', $date));
            });
    }
}
