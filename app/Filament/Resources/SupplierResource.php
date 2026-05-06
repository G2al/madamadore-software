<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Fornitori';
    protected static ?string $modelLabel = 'Fornitore';
    protected static ?string $pluralModelLabel = 'Fornitori';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome fornitore')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Cellulare con prefisso italiano')
                    ->tel()
                    ->required()
                    ->helperText('Puoi inserire 3331234567 oppure +393331234567.')
                    ->placeholder('+393331234567')
                    ->maxLength(20)
                    ->rule('regex:/^(\+39)?\d{9,11}$/'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Fornitore')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Cellulare')
                    ->searchable(),

                Tables\Columns\TextColumn::make('shopping_items_count')
                    ->label('Voci manuali')
                    ->counts('shoppingItems')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('dress_fabrics_count')
                    ->label('Tessuti automatici')
                    ->counts('dressFabrics')
                    ->badge()
                    ->color('warning'),
            ])
            ->actions([
                Action::make('print_supplier_list')
                    ->label('PDF Spesa')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn (Supplier $record): string => route('suppliers.shopping-list.print', $record))
                    ->openUrlInNewTab(),
                Action::make('whatsapp_supplier_list')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (Supplier $record): bool => filled($record->phone_number))
                    ->url(function (Supplier $record): string {
                        $pdfUrl = URL::temporarySignedRoute(
                            'suppliers.shopping-list.shared',
                            now()->addDays(7),
                            ['supplier' => $record],
                        );

                        $message = "Ciao {$record->name}, ti invio la lista della spesa da acquistare: {$pdfUrl}";

                        return 'https://wa.me/' . $record->whatsappDigits() . '?text=' . rawurlencode($message);
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
