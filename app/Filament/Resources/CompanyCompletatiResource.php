<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyCompletatiResource\Pages;
use App\Filament\Resources\CompanyAdjustmentResource\Concerns\HasCompanyAdjustmentFormSections;
use App\Models\CompanyAdjustment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyCompletatiResource extends Resource
{
    use HasCompanyAdjustmentFormSections;

    protected static ?string $model = CompanyAdjustment::class;

    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Aggiusti Aziende';
    protected static ?string $navigationLabel = 'Completati';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?int $navigationSort = 3;

    // Query per mostrare SOLO gli aggiusti aziendali completati
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'confermato');
    }

    // Form completo
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
                    ->color('info'),

                Tables\Columns\TextColumn::make('client_price')
                    ->label('Prezzo')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining')
                    ->label('Rimanente')
                    ->money('EUR')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Consegna')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record?->delivery_date && \Carbon\Carbon::parse($record->delivery_date)->isPast() ? 'danger' : 'success'),
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
            ])
            ->actions([
                // Azione per inviare WhatsApp al cliente
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        $phone = $record->customer?->phone_number;
                        if (!$phone) return null;
                        
                        $digits = preg_replace('/\D+/', '', $phone);
                        $message = "Ciao {$record->customer->name}, il tuo aggiusto aziendale è pronto, passa a ritirarlo appena puoi e grazie per aver scelto Madamadorè";
                        
                        return "https://wa.me/{$digits}?text=" . urlencode($message);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->customer?->phone_number),

                // Azione per consegnare l'aggiusto
                Tables\Actions\Action::make('consegna')
                    ->label('Consegna Aggiusto')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Conferma Consegna')
                    ->modalDescription('L\'aggiusto aziendale sarà segnato come consegnato e apparirà nella sezione "Consegnati".')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'consegnato',
                            'ritirato' => true
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Aggiusto Aziendale Consegnato!')
                            ->body("L'aggiusto aziendale per {$record->customer->name} è stato consegnato.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // Azione bulk per consegnare più aggiusti insieme
                    Tables\Actions\BulkAction::make('consegna_multipli')
                        ->label('Consegna Selezionati')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->count();
                            
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'consegnato',
                                    'ritirato' => true
                                ]);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title("$count aggiusti aziendali consegnati!")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('delivery_date', 'asc')
            ->emptyStateHeading('Nessun aggiusto aziendale completato')
            ->emptyStateDescription('Non ci sono ancora aggiusti aziendali completati da consegnare.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyCompletatis::route('/'),
            'edit' => Pages\EditCompanyCompletati::route('/{record}/edit'),
        ];
    }

    // Disabilita creazione
    public static function canCreate(): bool
    {
        return false;
    }

    // Badge con conteggio nel menu
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'confermato')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'confermato')->count();
        
        return match (true) {
            $count > 0 => 'primary',
            default => null
        };
    }
}