<?php

namespace App\Filament\Resources;

use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Resources\DressConsegnatoResource\Pages;

class DressConsegnatoResource extends Resource
{
    protected static ?string $model = Dress::class;
    
    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Consegnati';
    protected static ?string $modelLabel = 'Abito Consegnato';
    protected static ?string $pluralModelLabel = 'Abiti Consegnati';
    protected static ?int $navigationSort = 6;

    // Usa lo stesso form della DressResource principale
    public static function form(Form $form): Form
    {
        return DressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            // FILTRO AUTOMATICO INVISIBILE
            ->modifyQueryUsing(fn ($query) => $query->where('status', 'consegnato'))
            
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
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('delivery_date')
                    ->label('Data Consegna')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_client_price')
                    ->label('Prezzo Totale')
                    ->money('EUR')
                    ->color('success')
                    ->sortable()
                    ->visible(fn() => auth()->user()->role === 'admin'),

                // 🆕 TOGGLE RITIRATO
                ToggleColumn::make('ritirato')
                    ->label('Ritirato')
                    ->onColor('success')
                    ->offColor('info')
                    ->afterStateUpdated(function ($record, $state) {
                        \Filament\Notifications\Notification::make()
                            ->title('Stato aggiornato')
                            ->body($state ? 'Abito marcato come ritirato' : 'Abito marcato come non ritirato')
                            ->success()
                            ->send();
                    }),

                // 🆕 ICONA SALDATO CON AZIONE
                IconColumn::make('saldato')
                    ->label('Saldato')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->action(
                        Action::make('toggle_saldato')
                            ->label(fn($record) => $record->saldato ? 'Desalda' : 'Salda')
                            ->icon(fn($record) => $record->saldato ? 'heroicon-o-x-circle' : 'heroicon-o-banknotes')
                            ->color(fn($record) => $record->saldato ? 'warning' : 'success')
                            ->requiresConfirmation()
                            ->modalHeading(fn($record) => $record->saldato ? 'Desalda Abito' : 'Conferma Saldamento')
                            ->modalDescription(fn($record) => $record->saldato 
                                ? 'Vuoi annullare il saldamento? Il rimanente tornerà al prezzo totale.' 
                                : 'Totale da saldare: €' . number_format($record->total_client_price, 2, ',', '.'))
                            ->form(fn($record) => $record->saldato ? [] : [
                                \Filament\Forms\Components\Select::make('payment_method')
                                    ->label('Metodo di Pagamento')
                                    ->options([
                                        'contanti' => 'Contanti',
                                        'pos' => 'POS',
                                        'bonifico' => 'Bonifico',
                                        'altro' => 'Altro',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-banknotes'),

                                \Filament\Forms\Components\TextInput::make('payment_method_custom')
                                    ->label('Specifica Metodo')
                                    ->placeholder('Es: Assegno, PayPal, ecc.')
                                    ->required()
                                    ->visible(fn($get) => $get('payment_method') === 'altro')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-pencil'),
                            ])
                            ->action(function ($record, array $data) {
                                if ($record->saldato) {
                                    // DESALDA
                                    $record->update([
                                        'saldato' => false,
                                        'remaining' => $record->total_client_price,
                                        'payment_method' => null,
                                    ]);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Abito Desaldato')
                                        ->body('Rimanente da incassare: €' . number_format($record->total_client_price, 2, ',', '.'))
                                        ->warning()
                                        ->send();
                                } else {
                                    // SALDA con metodo pagamento
                                    $paymentMethod = $data['payment_method'] === 'altro' 
                                        ? $data['payment_method_custom'] 
                                        : $data['payment_method'];

                                    $record->update([
                                        'saldato' => true,
                                        'remaining' => 0,
                                        'payment_method' => $paymentMethod,
                                    ]);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Abito Saldato!')
                                        ->body('Importo: €' . number_format($record->total_client_price, 2, ',', '.') . ' | Metodo: ' . ucfirst($paymentMethod))
                                        ->success()
                                        ->send();
                                }
                            })
                    )
                    ->visible(fn() => auth()->user()->role === 'admin'),

                // 🆕 METODO PAGAMENTO
                TextColumn::make('payment_method')
                    ->label('Metodo Pagamento')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => $state ? ucfirst($state) : '-')
                    ->toggleable()
                    ->visible(fn() => auth()->user()->role === 'admin'),

                TextColumn::make('updated_at')
                    ->label('Consegnato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->tooltip('Data e ora di consegna effettiva'),

                // Mostra se ci sono note finali dalla fase precedente
                TextColumn::make('pronta_misura_notes')
                    ->label('Note Finali')
                    ->limit(30)
                    ->placeholder('Nessuna nota')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn($record) => $record->pronta_misura_notes)
                    ->icon(fn($record) => $record->pronta_misura_notes ? 'heroicon-o-document-text' : null)
                    ->color('warning'),
            ])
            
            // AZIONI SPECIFICHE PER QUESTO STATO
            ->actions([
                // Bottone per visualizzare riepilogo completo dell'abito
                Action::make('riepilogo_abito')
                    ->label('Riepilogo')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->modalHeading(fn($record) => "Riepilogo per {$record->customer_name}")
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Informazioni Cliente')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('customer_name')
                                    ->label('Nome Cliente'),
                                \Filament\Infolists\Components\TextEntry::make('phone_number')
                                    ->label('Telefono'),
                                \Filament\Infolists\Components\TextEntry::make('ceremony_type')
                                    ->label('Tipo Cerimonia'),
                                \Filament\Infolists\Components\TextEntry::make('ceremony_date')
                                    ->label('Data Cerimonia')
                                    ->date('d/m/Y'),
                                \Filament\Infolists\Components\TextEntry::make('ceremony_holder')
                                    ->label('Intestatario'),
                            ])
                            ->columns(2),
                            
                        \Filament\Infolists\Components\Section::make('Dettagli Economici')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('total_client_price')
                                    ->label('Prezzo Totale')
                                    ->money('EUR'),
                                \Filament\Infolists\Components\TextEntry::make('deposit')
                                    ->label('Acconto')
                                    ->money('EUR'),
                                \Filament\Infolists\Components\TextEntry::make('remaining')
                                    ->label('Rimanente')
                                    ->money('EUR')
                                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
                                    
                            ])
                            ->columns(3)
                            ->visible(fn() => auth()->user()->role === 'admin'),
                            
                        \Filament\Infolists\Components\Section::make('Note Finali')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('pronta_misura_notes')
                                    ->label('Note dalla fase Pronta Misura')
                                    ->placeholder('Nessuna nota specifica'),
                                \Filament\Infolists\Components\TextEntry::make('notes')
                                    ->label('Note Generali')
                                    ->placeholder('Nessuna nota generale'),
                            ])
                            ->visible(fn($record) => $record->pronta_misura_notes || $record->notes),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi')
                    ->slideOver(),
                
                // Bottone per visualizzare i tessuti utilizzati
                Action::make('vedi_tessuti')
                    ->label('Tessuti')
                    ->icon('heroicon-o-squares-2x2')
                    ->color('secondary')
                    ->modalHeading(fn($record) => "Tessuti per {$record->customer_name}")
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Tessuti Utilizzati')
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
            
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\BulkAction::make('archive')
                        ->label('Sposta nel Cestino')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Sposta nel Cestino')
                        ->modalDescription('Gli abiti selezionati verranno rimossi visivamente dal pannello ma resteranno conservati nel database per eventuali consultazioni future.')
                        ->modalSubmitActionLabel('Archivia')
                        ->action(function ($records): void {
                            $count = 0;

                            foreach ($records as $record) {
                                // Ricarica il record senza global scopes
                                $fresh = \App\Models\Dress::withoutGlobalScopes()->find($record->id);
                                if ($fresh) {
                                    $fresh->archive();
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Archiviazione completata')
                                ->body("{$count} abiti spostati nel cestino.")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('Nessun abito consegnato')
            ->emptyStateDescription('Non ci sono ancora abiti consegnati.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDressConsegnato::route('/'),
            'view' => Pages\ViewDressConsegnato::route('/{record}'),
            'edit' => Pages\EditDressConsegnato::route('/{record}/edit'),
        ];
    }

    // Disabilita la creazione diretta
    public static function canCreate(): bool
    {
        return false;
    }

    // Badge con conteggio
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'consegnato')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'consegnato')->count();
        
        return match (true) {
            $count > 20 => 'success',
            $count > 10 => 'info', 
            $count > 0 => 'primary',
            default => null
        };
    }
}