<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyInLavorazioneResource\Pages;
use App\Filament\Resources\CompanyAdjustmentResource\Concerns\HasCompanyAdjustmentFormSections;
use App\Models\CompanyAdjustment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyInLavorazioneResource extends Resource
{
    use HasCompanyAdjustmentFormSections;

    protected static ?string $model = CompanyAdjustment::class;

    // Configurazione navigazione
    protected static ?string $navigationGroup = 'Aggiusti Aziende';
    protected static ?string $navigationLabel = 'In Lavorazione';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 2;

    // Query per mostrare SOLO gli aggiusti aziendali in lavorazione
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'in_lavorazione');
    }

    // Form completo come la resource principale
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
                    
                Tables\Filters\Filter::make('scaduti')
                    ->label('Scaduti')
                    ->query(fn($query) => $query->whereDate('delivery_date', '<', now())),
            ])
            ->actions([
                // Azione per completare il lavoro
                Tables\Actions\Action::make('completa')
                    ->label('Completa Lavoro')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Conferma Completamento')
                    ->modalDescription('Il lavoro sarà segnato come completato e apparirà nella sezione "Completati".')
                    ->action(function ($record) {
                        $record->update(['status' => 'confermato']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Lavoro Completato!')
                            ->body("L'aggiusto aziendale per {$record->customer->name} è stato completato.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // Azione bulk per completare più lavori insieme
                    Tables\Actions\BulkAction::make('completa_multipli')
                        ->label('Completa Selezionati')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->count();
                            
                            foreach ($records as $record) {
                                $record->update(['status' => 'confermato']);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title("$count lavori aziendali completati!")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('delivery_date', 'asc')
            ->emptyStateHeading('Nessun aggiusto aziendale in lavorazione')
            ->emptyStateDescription('Tutti i lavori aziendali sono stati completati!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyInLavoraziones::route('/'),
            'edit' => Pages\EditCompanyInLavorazione::route('/{record}/edit'),
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
        return static::getModel()::where('status', 'in_lavorazione')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'in_lavorazione')->count();
        
        return match (true) {
            $count > 0 => 'warning',
            default => null
        };
    }
}