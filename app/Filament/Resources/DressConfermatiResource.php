<?php

namespace App\Filament\Resources;

use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DressConfermatiResource\Pages;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class DressConfermatiResource extends Resource
{
    protected static ?string $model = Dress::class;
    
    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Confermati';
    protected static ?string $modelLabel = 'Abito Confermato';
    protected static ?string $pluralModelLabel = 'Abiti Confermati';
    protected static ?int $navigationSort = 3;

    // Usa lo stesso form della DressResource principale
    public static function form(Form $form): Form
    {
        return DressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            // FILTRO AUTOMATICO INVISIBILE
            ->modifyQueryUsing(fn ($query) => $query->where('status', 'confermato'))
            
            // COLONNE SPECIFICHE PER QUESTO STATO
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn ($record) => $record->phone_number)
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),

                TextColumn::make('ceremony_type')
                    ->label('Cerimonia')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('ceremony_date')
                    ->label('Data Cerimonia')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_client_price')
                    ->label('Prezzo Totale')
                    ->money('EUR')
                    ->color('success')
                    ->sortable()
                    ->visible(fn() => auth()->user()->role === 'admin'),

                TextColumn::make('deposit')
                    ->label('Acconto')
                    ->money('EUR')
                    ->color('info')
                    ->sortable()
                    ->visible(fn() => auth()->user()->role === 'admin'),
            ])
            
            // AZIONI SPECIFICHE PER QUESTO STATO
            ->actions([
                // Bottone principale per questo stato
                Action::make('acquista_tessuti')
                    ->label('Acquista Tessuti')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(fn (Dress $record) => $record->update(['status' => 'da_tagliare']))
                    ->successNotificationTitle('Tessuti acquistati! Abito pronto per il taglio.'),
                
                // Bottone per visualizzare i tessuti (SOLO ADMIN)
                Action::make('vedi_tessuti')
                    ->label('Lista Tessuti')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->visible(fn() => auth()->user()->role === 'admin')
                    ->modalHeading(fn($record) => "Tessuti per {$record->customer_name}")
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Tessuti Necessari')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('fabrics')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('name')
                                            ->label('Tessuto')
                                            ->weight('bold')
                                            ->color('primary'),
                                        
                                        \Filament\Infolists\Components\TextEntry::make('color_code')
                                            ->label('Codice Colore')
                                            ->badge(),
                                        
                                        \Filament\Infolists\Components\TextEntry::make('meters')
                                            ->label('Metri')
                                            ->formatStateUsing(fn($state) => $state . ' mt')
                                            ->color('success'),
                                        
                                        \Filament\Infolists\Components\TextEntry::make('client_price')
                                            ->label('Prezzo/mt')
                                            ->money('EUR')
                                            ->color('warning'),
                                        
                                        \Filament\Infolists\Components\TextEntry::make('subtotal')
                                            ->label('Subtotale')
                                            ->state(fn($record) => $record->meters * $record->client_price)
                                            ->money('EUR')
                                            ->color('danger')
                                            ->weight('bold'),
                                    ])
                                    ->columns(3),
                                
                                \Filament\Infolists\Components\TextEntry::make('total_cost')
                                    ->label('TOTALE TESSUTI')
                                    ->state(fn($record) => $record->fabrics->sum(fn($f) => $f->meters * $f->client_price))
                                    ->money('EUR')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi')
                    ->slideOver(),
            ])
            
            // Bulk actions per selezione multipla
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDressConfermati::route('/'),
            'create' => Pages\CreateDressConfermati::route('/create'),
            'edit' => Pages\EditDressConfermati::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'confermato')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'confermato')->count();
        
        return match (true) {
            $count > 5 => 'danger',
            $count > 2 => 'warning', 
            $count > 0 => 'success',
            default => null
        };
    }
}