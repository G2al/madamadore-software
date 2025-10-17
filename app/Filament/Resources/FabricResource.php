<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FabricResource\Pages;
use App\Models\Fabric;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class FabricResource extends Resource
{
    protected static ?string $model = Fabric::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Inventario Tessuti';
    protected static ?string $modelLabel = 'Tessuto';
    protected static ?string $pluralModelLabel = 'Tessuti';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Tessuto')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('type')
                    ->label('Tipologia')
                    ->maxLength(255),

                Forms\Components\TextInput::make('color_code')
                    ->label('Codice Colore')
                    ->maxLength(255),

                Forms\Components\TextInput::make('supplier')
                    ->label('Fornitore')
                    ->maxLength(255),

                Forms\Components\TextInput::make('purchase_price')
                    ->label('Prezzo Acquisto')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€'),

                Forms\Components\TextInput::make('client_price')
                    ->label('Prezzo Cliente')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€'),

Forms\Components\FileUpload::make('image')
    ->label('Foto Tessuto')
    ->image()
    ->disk('public')
    ->directory('fabrics')
    ->visibility('public')
    ->imageEditor() // ← AGGIUNGI QUESTO per processare HEIC/orientamento
    ->imageEditorAspectRatios([null]) // ← AGGIUNGI QUESTO per qualsiasi proporzione
    ->maxSize(20480) // ← AGGIUNGI QUESTO per limite 20MB
    ->downloadable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipologia')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier')
                    ->label('Fornitore'),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Prezzo Acquisto')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('client_price')
                    ->label('Prezzo Cliente')
                    ->money('EUR'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFabrics::route('/'),
            'create' => Pages\CreateFabric::route('/create'),
            'edit' => Pages\EditFabric::route('/{record}/edit'),
        ];
    }
}
