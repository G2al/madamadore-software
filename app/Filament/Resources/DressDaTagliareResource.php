<?php

namespace App\Filament\Resources;

use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DressDaTagliareResource\Pages;

class DressDaTagliareResource extends Resource
{
    protected static ?string $model = Dress::class;
    
    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-scissors';
    protected static ?string $navigationLabel = 'Da Tagliare';
    protected static ?string $modelLabel = 'Abito da Tagliare';
    protected static ?string $pluralModelLabel = 'Abiti da Tagliare';
    protected static ?int $navigationSort = 4;

    // Usa lo stesso form della DressResource principale
    public static function form(Form $form): Form
    {
        return DressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            // FILTRO AUTOMATICO INVISIBILE
            ->modifyQueryUsing(fn ($query) => $query->where('status', 'da_tagliare'))
            
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
            ])
            
            // AZIONI SPECIFICHE PER QUESTO STATO
            ->actions([
                // Bottone principale per questo stato
                Action::make('pronto_misure')
                    ->label('Pronto per le Misure')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Dress $record) => $record->update(['status' => 'pronta_misura']))
                    ->successNotificationTitle('Abito pronto per le misure!'),
                    
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
                                
                                // Aggiungi anche i campi legacy non nella costante
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
                            
                        // AGGIUNGI QUESTA SEZIONE PER LE MISURE PERSONALIZZATE
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
        \Filament\Tables\Actions\BulkAction::make('archive')
            ->label('Sposta nel Cestino')
            ->icon('heroicon-o-archive-box')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Sposta nel Cestino')
            ->modalDescription('Gli abiti selezionati verranno rimossi visivamente dal pannello ma resteranno conservati nel database per il richiamo delle misure.')
            ->modalSubmitActionLabel('Archivia')
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    // 🔹 Ricarica il record senza global scopes
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

            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDressDaTagliare::route('/'),
            'create' => Pages\CreateDressDaTagliare::route('/create'),
            'edit' => Pages\EditDressDaTagliare::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'da_tagliare')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'da_tagliare')->count();
        
        return match (true) {
            $count > 5 => 'danger',
            $count > 2 => 'warning', 
            $count > 0 => 'primary',
            default => null
        };
    }
}