<?php

namespace App\Filament\Resources;

use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\DressInAttesaAccontoResource\Pages;

class DressInAttesaAccontoResource extends Resource
{
    protected static ?string $model = Dress::class;
    
    // Navigazione
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'In Attesa Acconto';
    protected static ?string $modelLabel = 'Abito in Attesa Acconto';
    protected static ?string $pluralModelLabel = 'Abiti in Attesa Acconto';
    protected static ?int $navigationSort = 2;

    // Usa lo stesso form principale
    public static function form(Form $form): Form
    {
        return DressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Mostra solo abiti in attesa di acconto
            ->modifyQueryUsing(fn ($query) => 
                $query->where('status', 'in_attesa_acconto')
            )
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

                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('acconto_ricevuto')
                    ->label('Acconto Ricevuto')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Dress $record) => $record->update(['status' => 'confermato']))
                    ->successNotificationTitle('Abito confermato! Acconto ricevuto.'),
            ])
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
                                // Ricarica il record completo senza global scope
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
            'index' => Pages\ListDressInAttesaAcconto::route('/'),
            'create' => Pages\CreateDressInAttesaAcconto::route('/create'),
            'edit' => Pages\EditDressInAttesaAcconto::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_attesa_acconto')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'in_attesa_acconto')->count();
        
        return match (true) {
            $count > 5 => 'danger',
            $count > 2 => 'warning', 
            $count > 0 => 'primary',
            default => null
        };
    }
}
