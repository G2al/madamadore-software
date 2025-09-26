<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers\PresencesRelationManager;
use App\Filament\Resources\StudentResource\RelationManagers\PaymentsRelationManager;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Presenze Studenti';
    protected static ?string $pluralLabel = 'Presenze Studenti';
    protected static ?string $modelLabel = 'Presenza Studente';
    protected static ?int $navigationSort = 2;

    // 👇 stessa tendina
    protected static ?string $navigationGroup = 'Presenze & Calendari';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('cognome')
                    ->label('Cognome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('telefono')
                    ->label('Telefono')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\TextInput::make('costo_lezione')
                    ->label('Costo Lezione (€)')
                    ->numeric()
                    ->required()
                    ->prefix('€'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('full_name')->label('Nome Completo')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('telefono')->label('Telefono'),
                Tables\Columns\TextColumn::make('costo_lezione')
                    ->label('Costo Lezione')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('presences_count')
                    ->counts('presences')
                    ->label('Presenze'),
                Tables\Columns\TextColumn::make('payments_sum_amount')
                    ->sum('payments', 'amount')
                    ->money('EUR')
                    ->label('Totale Pagato'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PresencesRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
