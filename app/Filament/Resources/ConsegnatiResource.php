<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsegnatiResource\Pages;
use App\Filament\Resources\AdjustmentResource\Concerns\HasAdjustmentFormSections;
use App\Models\Adjustment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsegnatiResource extends Resource
{
    use HasAdjustmentFormSections;

    protected static ?string $model = Adjustment::class;

    protected static ?string $navigationGroup = 'Aggiusti';
    protected static ?string $navigationLabel = 'Consegnati';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 4;

    // Query per mostrare SOLO gli aggiusti consegnati
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'consegnato');
    }

    // Form completo (solo visualizzazione per storico)
    public static function form(Form $form): Form
    {
        return $form->schema([
            self::clientSection(),
            self::paymentSection(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->description(fn($record) => $record->customer?->phone_number)
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('referente')
                    ->label('Referente')
                    ->searchable()
                    ->placeholder('N/D')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items_display')
                    ->label('Aggiusti')
                    ->state(function ($record) {
                        $items = $record->items;
                        $count = $items->count();
                        
                        if ($count === 0) return 'Nessun aggiusto';
                        
                        $firstName = $items->first()->name;
                        return $count === 1 ? $firstName : $firstName . " + " . ($count - 1) . " altri";
                    })
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('client_price')
                    ->label('Prezzo')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining')
                    ->label('Rimanente')
                    ->money('EUR')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success')
                    ->icon(fn($state) => $state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),

                Tables\Columns\ToggleColumn::make('ritirato')
                    ->label('Ritirato')
                    ->onColor('success')
                    ->offColor('info')
                    ->afterStateUpdated(function ($record, $state) {
                        \Filament\Notifications\Notification::make()
                            ->title('Stato aggiornato')
                            ->body($state ? 'Aggiusto marcato come ritirato' : 'Aggiusto marcato come non ritirato')
                            ->success()
                            ->send();
                    }),

Tables\Columns\IconColumn::make('saldato')
    ->label('Saldato')
    ->boolean()
    ->trueIcon('heroicon-o-check-circle')
    ->falseIcon('heroicon-o-x-circle')
    ->trueColor('success')
    ->falseColor('warning')
    ->action(
        Tables\Actions\Action::make('toggle_saldato')
            ->label(fn($record) => $record->saldato ? 'Desalda' : 'Salda')
            ->icon(fn($record) => $record->saldato ? 'heroicon-o-x-circle' : 'heroicon-o-banknotes')
            ->color(fn($record) => $record->saldato ? 'warning' : 'success')
            ->requiresConfirmation()
            ->modalHeading(fn($record) => $record->saldato ? 'Desalda Aggiusto' : 'Conferma Saldamento')
            ->modalDescription(fn($record) => $record->saldato 
                ? 'Vuoi annullare il saldamento? L\'acconto sarà azzerato.' 
                : 'Totale da saldare: €' . number_format($record->remaining, 2, ',', '.'))
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
        ->prefixIcon('heroicon-o-banknotes'), // Icona singola per il campo

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
                        'deposit' => 0,
                        'remaining' => $record->client_price,
                        'payment_method' => null,
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Aggiusto Desaldato')
                        ->body('Rimanente da incassare: €' . number_format($record->client_price, 2, ',', '.'))
                        ->warning()
                        ->send();
                } else {
                    // SALDA con metodo pagamento
                    $paymentMethod = $data['payment_method'] === 'altro' 
                        ? $data['payment_method_custom'] 
                        : $data['payment_method'];

                    $record->update([
                        'saldato' => true,
                        'deposit' => $record->client_price,
                        'remaining' => 0,
                        'payment_method' => $paymentMethod,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Aggiusto Saldato!')
                        ->body('Importo: €' . number_format($record->client_price, 2, ',', '.') . ' | Metodo: ' . ucfirst($paymentMethod))
                        ->success()
                        ->send();
                }
            })
    ),

Tables\Columns\TextColumn::make('payment_method')
    ->label('Metodo Pagamento')
    ->badge()
    ->color('info')
    ->formatStateUsing(fn($state) => $state ? ucfirst($state) : '-')
    ->toggleable(),
])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name'),
                    
                Tables\Filters\Filter::make('saldato')
                    ->label('Saldato')
                    ->query(fn($query) => $query->where('remaining', '=', 0)),
                    
                Tables\Filters\Filter::make('non_saldato')
                    ->label('Non saldato')
                    ->query(fn($query) => $query->where('remaining', '>', 0)),

                Tables\Filters\Filter::make('questo_mese')
                    ->label('Questo mese')
                    ->query(fn($query) => $query->whereMonth('updated_at', now()->month)
                                                ->whereYear('updated_at', now()->year)),
            ])
            ->actions([
                // Azione per scaricare la ricevuta
                Tables\Actions\Action::make('ricevuta')
                    ->label('Ricevuta')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn($record) => route('adjustments.receipt', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // Azione bulk per scaricare ricevute multiple
                    Tables\Actions\BulkAction::make('ricevute_multiple')
                        ->label('Scarica Ricevute')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            \Filament\Notifications\Notification::make()
                                ->title('Ricevute Multiple')
                                ->body('Funzionalità in sviluppo - scarica le ricevute singolarmente.')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('Nessun aggiusto consegnato')
            ->emptyStateDescription('Non ci sono ancora aggiusti consegnati.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsegnatis::route('/'),
            'view' => Pages\ViewConsegnati::route('/{record}'),
        ];
    }

    // Disabilita creazione e modifica
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false; // Non si modificano aggiusti già consegnati
    }

    // Badge con conteggio nel menu
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'consegnato')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}