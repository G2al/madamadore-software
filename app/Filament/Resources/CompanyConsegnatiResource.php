<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyConsegnatiResource\Pages;
use App\Filament\Resources\CompanyAdjustmentResource\Concerns\HasCompanyAdjustmentFormSections;
use App\Models\CompanyAdjustment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyConsegnatiResource extends Resource
{
    use HasCompanyAdjustmentFormSections;

    protected static ?string $model = CompanyAdjustment::class;

    protected static ?string $navigationGroup = 'Aggiusti Aziende';
    protected static ?string $navigationLabel = 'Consegnati';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 4;

    // Query per mostrare SOLO gli aggiusti aziendali consegnati
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
                            ->body($state ? 'Aggiusto aziendale marcato come ritirato' : 'Aggiusto aziendale marcato come non ritirato')
                            ->success()
                            ->send();
                    }),

                Tables\Columns\ToggleColumn::make('saldato')
                    ->label('Saldato')
                    ->onColor('success')
                    ->offColor('warning')
                    ->afterStateUpdated(function ($record, $state) {
                        \Filament\Notifications\Notification::make()
                            ->title('Stato aggiornato')
                            ->body($state ? 'Aggiusto aziendale marcato come saldato' : 'Aggiusto aziendale marcato come non saldato')
                            ->success()
                            ->send();
                    }),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Data Consegna')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Consegnato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->url(fn($record) => route('company-adjustments.receipt', $record))
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
            ->emptyStateHeading('Nessun aggiusto aziendale consegnato')
            ->emptyStateDescription('Non ci sono ancora aggiusti aziendali consegnati.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyConsegnatis::route('/'),
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