<?php

namespace App\Filament\Resources;

use App\Models\SpecialDress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get; // 👈
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder; // 👈
use App\Filament\Resources\SpecialDressConsegnatoResource\Pages;

class SpecialDressConsegnatoResource extends Resource
{
    protected static ?string $model = SpecialDress::class;

    protected static ?string $navigationGroup = 'Abiti Speciali';
    protected static ?string $navigationIcon  = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Consegnati (Speciali)';
    protected static ?string $modelLabel = 'Abito Speciale Consegnato';
    protected static ?string $pluralModelLabel = 'Abiti Speciali Consegnati';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return SpecialDressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'consegnato')) // 👈 tipizzato
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn (SpecialDress $record): ?string => $record->phone_number) // 👈 tipizzato
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('ceremony_type')
                    ->label('Festività')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-') // 👈 tipizzato
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Consegna')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_client_price')
                    ->label('Prezzo Totale')
                    ->money('EUR')
                    ->color('success')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Columns\ToggleColumn::make('ritirato')
                    ->label('Ritirato')
                    ->onColor('success')
                    ->offColor('info')
                    ->afterStateUpdated(function (SpecialDress $record, bool $state): void { // 👈 tipizzato
                        \Filament\Notifications\Notification::make()
                            ->title('Stato aggiornato')
                            ->body($state ? 'Abito marcato come ritirato' : 'Abito marcato come non ritirato')
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
                        Action::make('toggle_saldato')
                            ->label(fn (SpecialDress $record): string => $record->saldato ? 'Desalda' : 'Salda') // 👈 tipizzato
                            ->icon(fn (SpecialDress $record): string => $record->saldato ? 'heroicon-o-x-circle' : 'heroicon-o-banknotes')
                            ->color(fn (SpecialDress $record): string => $record->saldato ? 'warning' : 'success')
                            ->requiresConfirmation()
                            ->modalHeading(fn (SpecialDress $record): string => $record->saldato ? 'Desalda abito' : 'Conferma saldo')
                            ->modalDescription(fn (SpecialDress $record): string => $record->saldato
                                ? 'Vuoi annullare il saldo? Il rimanente tornerà al totale.'
                                : 'Totale da saldare: €' . number_format((float) $record->total_client_price, 2, ',', '.'))
                            ->form(fn (SpecialDress $record): array => $record->saldato ? [] : [ // 👈 tipizzato
                                Forms\Components\Select::make('payment_method')
                                    ->label('Metodo di pagamento')
                                    ->options([
                                        'contanti' => 'Contanti',
                                        'pos'      => 'POS',
                                        'bonifico' => 'Bonifico',
                                        'altro'    => 'Altro',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-banknotes'),
                                Forms\Components\TextInput::make('payment_method_custom')
                                    ->label('Specifica metodo')
                                    ->visible(fn (Get $get): bool => $get('payment_method') === 'altro') // 👈 tipizzato
                                    ->required(fn (Get $get): bool => $get('payment_method') === 'altro') // 👈 tipizzato
                                    ->maxLength(255),
                            ])
                            ->action(function (SpecialDress $record, array $data): void { // 👈 tipizzato
                                if ($record->saldato) {
                                    // DESALDA
                                    $record->update([
                                        'saldato' => false,
                                        'remaining' => (float) $record->total_client_price - (float) ($record->deposit ?? 0),
                                        'payment_method' => null,
                                    ]);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Abito desaldato')
                                        ->body('Rimanente: €' . number_format((float) $record->remaining, 2, ',', '.'))
                                        ->warning()
                                        ->send();
                                } else {
                                    // SALDA
                                    $pm = ($data['payment_method'] ?? null) === 'altro'
                                        ? ($data['payment_method_custom'] ?? 'Altro')
                                        : ($data['payment_method'] ?? 'Altro');

                                    $record->update([
                                        'saldato' => true,
                                        'remaining' => 0,
                                        'payment_method' => $pm,
                                    ]);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Abito saldato!')
                                        ->body('Importo: €' . number_format((float) $record->total_client_price, 2, ',', '.') . ' | Metodo: ' . ucfirst($pm))
                                        ->success()
                                        ->send();
                                }
                            })
                    )
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metodo pagamento')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-') // 👈 tipizzato
                    ->toggleable()
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Consegnato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->groups([
                Group::make('ceremony_type')
                    ->label('Festività')
                    ->collapsible(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->defaultGroup('ceremony_type')
            ->emptyStateHeading('Nessun abito consegnato (Special)')
            ->emptyStateDescription('Quando consegnerai un abito speciale comparirà qui.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpecialDressConsegnato::route('/'),
            'create' => Pages\CreateSpecialDressConsegnato::route('/create'),
            'edit'   => Pages\EditSpecialDressConsegnato::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'consegnato')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $c = static::getModel()::where('status', 'consegnato')->count();
        return match (true) {
            $c > 20 => 'success',
            $c > 10 => 'info',
            $c > 0  => 'primary',
            default => null
        };
    }
}
