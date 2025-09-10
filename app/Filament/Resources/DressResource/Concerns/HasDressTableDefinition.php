<?php

namespace App\Filament\Resources\DressResource\Concerns;

use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Filters\StatusFilter;
use App\Filament\Filters\CeremonyTypeFilter;
use App\Filament\Filters\DeliveryDateFilter;

trait HasDressTableDefinition
{
    /**
     * Definisce tutte le colonne della tabella Dress.
     *
     * @return array
     */
    protected static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('customer_name')
                ->label('Cliente')
                ->description(fn ($record) => $record->phone_number)
                ->searchable()
                ->sortable()
                ->icon('heroicon-o-user')
                ->weight('bold'),

            Tables\Columns\TextColumn::make('ceremony_type')
                ->label('Cerimonia')
                ->formatStateUsing(fn ($state) => ucfirst($state))
                ->badge()
                ->color('info')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Stato')
                ->badge()
                ->formatStateUsing(fn (?string $state) => self::getStatusLabels()[$state] ?? '-')
                ->color(fn (?string $state) => self::getStatusColors()[$state] ?? 'gray')
                ->sortable(),

            Tables\Columns\TextColumn::make('delivery_date')
                ->label('Consegna')
                ->date('d/m/Y')
                ->sortable()
                ->color(fn ($record) =>
                    $record?->delivery_date?->isPast()
                        ? 'danger'
                        : 'success'
                )
                ->description(fn ($record) =>
                    $record?->delivery_date?->isPast()
                        ? 'Scaduta'
                        : 'In tempo'
                ),

            Tables\Columns\TextColumn::make('deposit')
                ->label('Acconto')
                ->money('EUR')
                ->color('info')
                ->sortable(),
        ];
    }


    /**
     * Definisce i filtri disponibili nella tabella Dress.
     *
     * @return array
     */
    protected static function tableFilters(): array
    {
        return [
            StatusFilter::make(),
            CeremonyTypeFilter::make(),
            DeliveryDateFilter::make(),
        ];
    }

    /**
     * Definisce le azioni disponibili per ogni riga.
     *
     * @return array
     */
    protected static function tableRowActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    /**
     * Definisce le bulk actions disponibili nella tabella Dress.
     *
     * @return array
     */
    protected static function tableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    /**
     * Costruisce la tabella Dress completa.
     *
     * @param Table $table
     * @return Table
     */
    public static function buildTable(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters(self::tableFilters())
            ->actions(self::tableRowActions())
            ->bulkActions(self::tableBulkActions())
            ->defaultSort('created_at', 'desc');
    }
}
