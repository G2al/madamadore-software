<?php

namespace App\Filament\Resources;

use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DressProntaMisuraResource\Pages;

class DressProntaMisuraResource extends Resource
{
    protected static ?string $model = Dress::class;
    
    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Pronta Misura';
    protected static ?string $modelLabel = 'Abito Pronta Misura';
    protected static ?string $pluralModelLabel = 'Abiti Pronta Misura';
    protected static ?int $navigationSort = 5;

    // Usa lo stesso form della DressResource principale
    public static function form(Form $form): Form
    {
        return DressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            // FILTRO AUTOMATICO INVISIBILE
            ->modifyQueryUsing(fn ($query) => $query->where('status', 'pronta_misura'))
            
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

                TextColumn::make('delivery_date')
                    ->label('Data Consegna')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) =>
                        $record?->delivery_date?->isPast()
                            ? 'danger'
                            : 'success'
                    ),

                TextColumn::make('total_client_price')
                    ->label('Prezzo Totale')
                    ->money('EUR')
                    ->color('success')
                    ->sortable()
                    ->visible(fn() => auth()->user()->role === 'admin'),

                // Colonna per visualizzare se ci sono note finali
                TextColumn::make('pronta_misura_notes')
                    ->label('Note Finali')
                    ->limit(30)
                    ->placeholder('Nessuna nota')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn($record) => $record->pronta_misura_notes)
                    ->icon(fn($record) => $record->pronta_misura_notes ? 'heroicon-o-document-text' : null)
                    ->color(fn($record) => $record->pronta_misura_notes ? 'warning' : 'gray'),
            ])
            
            // AZIONI SPECIFICHE PER QUESTO STATO
            ->actions([
                // Bottone principale per questo stato
                Action::make('consegna_abito')
                    ->label('Consegna Abito')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Conferma Consegna')
                    ->modalDescription(fn ($record) => "Confermi di voler consegnare l'abito di {$record->customer_name}?")
                    ->action(fn (Dress $record) => $record->update(['status' => 'consegnato']))
                    ->successNotificationTitle('Abito consegnato con successo!'),
                
                // Bottone per gestire le note specifiche di questa fase
                Action::make('gestisci_note')
                    ->label('Note Finali')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->modalHeading(fn($record) => "Note finali per {$record->customer_name}")
                    ->modalDescription('Note visibili solo nella fase "Pronta Misura"')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('pronta_misura_notes')
                            ->label('Note Pronta Misura')
                            ->rows(4)
                            ->placeholder('Inserisci note specifiche per questa fase: aggiustamenti finali, particolarità, istruzioni per la consegna...')
                            ->maxLength(1000)
                            ->helperText('Queste note sono visibili solo quando l\'abito è in fase "Pronta Misura"'),
                    ])
                    ->fillForm(fn($record) => [
                        'pronta_misura_notes' => $record->pronta_misura_notes
                    ])
                    ->action(function($record, $data) {
                        $record->update([
                            'pronta_misura_notes' => $data['pronta_misura_notes']
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title($data['pronta_misura_notes'] ? 'Note salvate!' : 'Note rimosse!')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Salva Note')
                    ->slideOver(),
                
                // Bottone per visualizzare le misure
                Action::make('visualizza_misure')
                    ->label('Visualizza Misure')
                    ->icon('heroicon-o-user')
                    ->color('info')
                    ->modalHeading(fn($record) => "Misure per {$record->customer_name}")
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Misure del Cliente')
                            ->schema(function() {
                                $schema = [];
                                
                                // Genera automaticamente TUTTI i campi dalla costante ORDERED_MEASURES
                                foreach (\App\Models\DressMeasurement::ORDERED_MEASURES as $field => $label) {
                                    $suffix = $field === 'inclinazione_spalle' ? '°' : 'cm';
                                    
                                    $schema[] = \Filament\Infolists\Components\TextEntry::make("measurements.{$field}")
                                        ->label($label)
                                        ->suffix(" {$suffix}")
                                        ->placeholder('Non inserita');
                                }
                                
                                // Aggiungi anche i campi legacy
                                $legacyFields = [
                                    'spalle' => 'Spalle (legacy)',
                                    'fianchi' => 'Fianchi (legacy)',
                                    'lunghezza_busto' => 'Lunghezza Busto (legacy)',
                                    'altezza_totale' => 'Altezza Totale (legacy)',
                                ];
                                
                                foreach ($legacyFields as $field => $label) {
                                    $schema[] = \Filament\Infolists\Components\TextEntry::make("measurements.{$field}")
                                        ->label($label)
                                        ->suffix(' cm')
                                        ->placeholder('Non inserita');
                                }
                                
                                return $schema;
                            })
                            ->columns(3),
                            
                        // Sezione misure personalizzate
                        \Filament\Infolists\Components\Section::make('Misure Personalizzate')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('customMeasurements')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('label')
                                            ->label('Nome Misura')
                                            ->weight('bold'),
                                        \Filament\Infolists\Components\TextEntry::make('value')
                                            ->label('Valore')
                                            ->suffix(' cm')
                                            ->placeholder('Non specificato'),
                                        \Filament\Infolists\Components\TextEntry::make('notes')
                                            ->label('Note')
                                            ->placeholder('Nessuna nota'),
                                    ])
                                    ->columns(3)
                            ])
                            ->visible(fn ($record) => $record->customMeasurements->count() > 0),
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
            'index' => Pages\ListDressProntaMisura::route('/'),
            'create' => Pages\CreateDressProntaMisura::route('/create'),
            'edit' => Pages\EditDressProntaMisura::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pronta_misura')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pronta_misura')->count();
        
        return match (true) {
            $count > 5 => 'danger',
            $count > 2 => 'warning', 
            $count > 0 => 'success',
            default => null
        };
    }
}