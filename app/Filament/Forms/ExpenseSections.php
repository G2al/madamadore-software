<?php

namespace App\Filament\Forms;

use App\Models\Supplier;
use Filament\Forms;

class ExpenseSections
{
    public static function make(string $photoDirectory): Forms\Components\Group
    {
        return Forms\Components\Group::make()
            ->schema([
                Forms\Components\Section::make('Articoli e campioni già disponibili')
                    ->description('Foto campione e materiali già disponibili: non vengono aggiunti alla Lista della Spesa.')
                    ->schema([self::availableItemsRepeater($photoDirectory)]),
                Forms\Components\Section::make('Cose da comprare')
                    ->description('Inserisci qui soltanto i materiali da acquistare: vengono aggiunti automaticamente alla Lista della Spesa.')
                    ->schema([self::purchaseItemsRepeater($photoDirectory)]),
            ])
            ->columnSpanFull();
    }

    private static function availableItemsRepeater(string $photoDirectory): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('availableExpenses')
            ->label('Articoli disponibili / Foto campione')
            ->relationship('availableExpenses')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome o descrizione')
                    ->placeholder('Es. Campione bottone, decorazione già disponibile...')
                    ->maxLength(255),
                self::photoField($photoDirectory),
                Forms\Components\TextInput::make('price')
                    ->label('Costo interno (EUR)')
                    ->numeric()
                    ->prefix('EUR')
                    ->default(0)
                    ->helperText('Non influisce sul prezzo cliente.'),
            ])
            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => [
                ...$data,
                'is_to_purchase' => false,
            ])
            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => [
                ...$data,
                'is_to_purchase' => false,
            ])
            ->columns(3)
            ->addActionLabel('Aggiungi articolo o campione')
            ->collapsible()
            ->cloneable();
    }

    private static function purchaseItemsRepeater(string $photoDirectory): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('purchaseExpenses')
            ->label('Materiali da acquistare')
            ->relationship('purchaseExpenses')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome articolo')
                    ->placeholder('Es. Gross grain, bottone, filo, cerniera...')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('color_code')
                    ->label('Codice colore')
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantità')
                    ->numeric()
                    ->step(0.01)
                    ->default(1)
                    ->required(),
                Forms\Components\Select::make('unit_type')
                    ->label('Unità')
                    ->options(['pezzi' => 'Pezzi', 'metri' => 'Metri'])
                    ->default('pezzi')
                    ->required(),
                Forms\Components\Select::make('supplier_id')
                    ->label('Fornitore')
                    ->relationship('supplierRecord', 'name')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn (Supplier $record): string => $record->name),
                self::photoField($photoDirectory),
                Forms\Components\TextInput::make('price')
                    ->label('Costo interno (EUR)')
                    ->numeric()
                    ->prefix('EUR')
                    ->default(0)
                    ->helperText('Non influisce sul prezzo cliente.'),
            ])
            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => [
                ...$data,
                'is_to_purchase' => true,
            ])
            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => [
                ...$data,
                'is_to_purchase' => true,
            ])
            ->columns(3)
            ->addActionLabel('Aggiungi cosa da comprare')
            ->collapsible()
            ->cloneable();
    }

    private static function photoField(string $photoDirectory): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('photo_path')
            ->label('Foto')
            ->image()
            ->directory($photoDirectory)
            ->imageEditor();
    }
}
