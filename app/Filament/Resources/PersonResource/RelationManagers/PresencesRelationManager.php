<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\Presence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PresencesRelationManager extends RelationManager
{
    protected static string $relationship = 'presences'; // -> Person::presences()

    protected static ?string $title = 'Presenze';
    protected static ?string $recordTitleAttribute = 'date';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->label('Data presenza')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required()
                ->default(now()),

            Forms\Components\Select::make('shift_type')
                ->label('Turno')
                ->options(Presence::SHIFT_TYPES)
                ->default('full_day')
                ->required()
                ->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('shift_type')
                    ->label('Turno')
                    ->formatStateUsing(fn (?string $state) => Presence::SHIFT_TYPES[$state] ?? $state ?? '-')
                    ->badge()
                    ->color(fn (?string $state) => $state === 'half_day' ? 'warning' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('questo_mese')
                    ->label('Questo mese')
                    ->query(fn ($q) => $q
                        ->whereBetween('date', [
                            now()->startOfMonth()->toDateString(),
                            now()->endOfMonth()->toDateString(),
                        ])),

                Tables\Filters\SelectFilter::make('shift_type')
                    ->label('Turno')
                    ->options(Presence::SHIFT_TYPES),
            ])
            ->groups([
                Tables\Grouping\Group::make('shift_type')
                    ->label('Turno')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Presence $record): string => Presence::SHIFT_TYPES[$record->shift_type] ?? $record->shift_type ?? '-')
                    ->collapsible(),
            ])
            ->defaultGroup('shift_type')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Aggiungi Presenza'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifica'),
                Tables\Actions\DeleteAction::make()->label('Elimina'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
