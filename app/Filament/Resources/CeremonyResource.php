<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CeremonyResource\Pages;
use App\Filament\Resources\CeremonyResource\RelationManagers;
use App\Models\Ceremony;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CeremonyResource extends Resource
{
    protected static ?string $model = Ceremony::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Festività';
    protected static ?string $modelLabel = 'Festività';
    protected static ?string $navigationGroup = 'Abiti Speciali';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Festività')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('icon')
                    ->label('Emoji/Icona')
                    ->hint('Es: 💒, 👶, 🎂, etc')
                    ->maxLength(10),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordine Visualizzazione')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icona')
                    ->width('80px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome Festività')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creata')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCeremonies::route('/'),
            'create' => Pages\CreateCeremony::route('/create'),
            'edit' => Pages\EditCeremony::route('/{record}/edit'),
        ];
    }
}
