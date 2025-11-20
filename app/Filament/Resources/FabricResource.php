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
            // 👇 SEZIONE 1: Informazioni Base (2 colonne)
            Forms\Components\Section::make('Informazioni Tessuto')
                ->schema([
                    Forms\Components\Grid::make(2)
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
                        ]),
                ])
                ->collapsible(),

            // 👇 SEZIONE 2: Foto Principale (intera larghezza)
            Forms\Components\Section::make('Foto Principale')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Foto Tessuto Principale')
                        ->image()
                        ->disk('public')
                        ->directory('fabrics')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageEditorAspectRatios([null])
                        ->maxSize(20480)
                        ->downloadable()
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            // 👇 SEZIONE 3: Fantasie (Grid compatto)
            Forms\Components\Section::make('Fantasie')
                ->schema([
                    Forms\Components\Repeater::make('patterns')
                        ->relationship('patterns')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome Fantasia')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\FileUpload::make('image')
                                        ->label('Foto Fantasia')
                                        ->image()
                                        ->disk('public')
                                        ->directory('fabric-patterns')
                                        ->visibility('public')
                                        ->imageEditor()
                                        ->imageEditorAspectRatios([null])
                                        ->maxSize(20480)
                                        ->downloadable()
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->collapsible()
                        ->collapsed() // 👈 Fantasie chiuse di default
                        ->cloneable()
                        ->defaultItems(0)
                        ->addActionLabel('Aggiungi Fantasia')
                        ->reorderable()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Nuova Fantasia'),
                ])
                ->collapsible(),
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
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('view_image')
                    ->label('Visualizza Foto')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Foto Tessuto')
                    ->modalContent(fn ($record) => view('filament.modals.fabric-image-modal', ['record' => $record]))
                    ->modal(),

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
