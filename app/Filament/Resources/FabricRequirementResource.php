<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DressResource;
use App\Filament\Resources\FabricRequirementResource\Pages;
use App\Models\DressFabric;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FabricRequirementResource extends Resource
{
    protected static ?string $model = DressFabric::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Requisiti Tessuti';
    protected static ?string $modelLabel = 'Requisito Tessuto';
    protected static ?string $pluralModelLabel = 'Requisiti Tessuti';
    protected static ?int $navigationSort = 40;

    /** Solo tessuti degli abiti "confermato" + calcolo Subtotale lato SQL */
    public static function getEloquentQuery(): Builder
    {
        return DressFabric::query()
            ->with([
                'dress:id,customer_name,status',
            ])
            ->select([
                'dress_fabrics.*',
                DB::raw('(COALESCE(meters,0) * COALESCE(purchase_price,0)) as row_total'),
            ])
            ->whereHas('dress', fn ($q) => $q->where('status', 'confermato'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tessuto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipologia')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier')
                    ->label('Fornitore')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dress.customer_name')
                    ->label('Cliente (Abito)')
                    ->url(fn ($record) => DressResource::getUrl('edit', ['record' => $record->dress_id]))
                    ->openUrlInNewTab()
                    ->searchable()
                    ->sortable(),

                                // Metri
                Tables\Columns\TextColumn::make('meters')
                    ->label('Metri')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.'))
                    ->suffix(' mt')
                    ->alignRight()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Metri totali')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.') . ' mt')
                    ),

                /* €/m */
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('€/m')
                    ->formatStateUsing(fn ($state) => '€ ' . number_format((float) $state, 2, ',', '.'))
                    ->alignRight(),

                /* Subtotale (alias SQL: row_total) */
                Tables\Columns\TextColumn::make('row_total')
                    ->label('Subtotale')
                    ->formatStateUsing(fn ($state) => '€ ' . number_format((float) $state, 2, ',', '.'))
                    ->alignRight()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Totale €')
                            ->formatStateUsing(fn ($state) => '€ ' . number_format((float) $state, 2, ',', '.'))
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('name')
                    ->label('Tessuto')
                    ->options(fn () => DressFabric::query()
                        ->whereHas('dress', fn ($q) => $q->where('status', 'confermato'))
                        ->orderBy('name')
                        ->pluck('name', 'name')
                        ->unique()
                        ->toArray()
                    ),

                Tables\Filters\SelectFilter::make('supplier')
                    ->label('Fornitore')
                    ->options(fn () => DressFabric::query()
                        ->whereHas('dress', fn ($q) => $q->where('status', 'confermato'))
                        ->orderBy('supplier')
                        ->pluck('supplier', 'supplier')
                        ->unique()
                        ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('apri_abito')
                    ->label('Apri Abito')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => DressResource::getUrl('edit', ['record' => $record->dress_id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]) // niente bulk in questa vista
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFabricRequirements::route('/'),
        ];
    }
}
