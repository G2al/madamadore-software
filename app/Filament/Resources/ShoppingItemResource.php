<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShoppingItemResource\Pages;
use App\Models\ShoppingItem;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class ShoppingItemResource extends Resource
{
    protected static ?string $model = ShoppingItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Lista della Spesa';
    protected static ?string $modelLabel = 'Voce Spesa';
    protected static ?string $pluralModelLabel = 'Lista della Spesa';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('price')
                    ->label('Prezzo (€)')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€')
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantità')
                    ->numeric()
                    ->step(0.01)
                    ->suffix(fn(Forms\Get $get) => $get('unit_type') === 'metri' ? 'mt' : 'pz')
                    ->required(),

                Forms\Components\Select::make('unit_type')
                    ->label('Tipo Misura')
                    ->options([
                        'pezzi' => 'Pezzi',
                        'metri' => 'Metri',
                    ])
                    ->default('pezzi')
                    ->required(),

                Forms\Components\TextInput::make('supplier')
                    ->label('Fornitore')
                    ->maxLength(255),

                Forms\Components\FileUpload::make('photo_path')
                    ->label('Foto')
                    ->directory('shopping-items')
                    ->image()
                    ->imageEditor()
                    ->previewable(),

                Forms\Components\DateTimePicker::make('purchase_date')
                    ->label('Data Acquisto')
                    ->disabled()
                    ->visible(fn($context) => $context === 'edit'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->square()
                    ->height(60)
                    ->circular(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prezzo')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantità')
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . ($record->unit_type === 'metri' ? 'mt' : 'pz')),

                Tables\Columns\TextColumn::make('supplier')
                    ->label('Fornitore')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Data Acquisto')
                    ->date('d/m/Y H:i')
                    ->color(fn($record) => $record->isPaid() ? 'success' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->label('Tipo Misura')
                    ->options([
                        'pezzi' => 'Pezzi',
                        'metri' => 'Metri',
                    ]),
                Tables\Filters\TernaryFilter::make('purchase_date')
                    ->label('Saldato')
                    ->trueLabel('Saldati')
                    ->falseLabel('Non saldati')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('purchase_date'),
                        false: fn($query) => $query->whereNull('purchase_date'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('salda')
                    ->label('Salda')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => !$record->isPaid())
                    ->action(function (ShoppingItem $record) {
                        $record->update(['purchase_date' => now()]);

                        Notification::make()
                            ->title('Elemento saldato con successo!')
                            ->success()
                            ->send();
                    }),
                Action::make('stampa')
                    ->label('Stampa')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn($record) => route('shopping-items.print.single', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('stampa_tutti')
                    ->label('Stampa Tutto')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(route('shopping-items.print.all'))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShoppingItems::route('/'),
            'create' => Pages\CreateShoppingItem::route('/create'),
            'edit' => Pages\EditShoppingItem::route('/{record}/edit'),
        ];
    }
}
