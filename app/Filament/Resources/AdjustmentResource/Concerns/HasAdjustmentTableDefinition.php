<?php

namespace App\Filament\Resources\AdjustmentResource\Concerns;

use Filament\Tables;
use Filament\Tables\Table;

trait HasAdjustmentTableDefinition
{
    public static function buildTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nome'),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente'),
                Tables\Columns\TextColumn::make('phone_number')->label('Telefono'),
                Tables\Columns\TextColumn::make('client_price')->label('Prezzo')->money('EUR'),
                Tables\Columns\TextColumn::make('deposit')->label('Acconto')->money('EUR'),
                Tables\Columns\TextColumn::make('total')->label('Totale')->money('EUR'),
                Tables\Columns\TextColumn::make('remaining')->label('Rimanente')->money('EUR'),
                Tables\Columns\TextColumn::make('profit')->label('Guadagno')->money('EUR'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
