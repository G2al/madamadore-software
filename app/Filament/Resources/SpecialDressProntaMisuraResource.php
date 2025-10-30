<?php

namespace App\Filament\Resources;

use App\Models\SpecialDress;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // 👈
use App\Filament\Resources\SpecialDressProntaMisuraResource\Pages;

class SpecialDressProntaMisuraResource extends Resource
{
    protected static ?string $model = SpecialDress::class;

    protected static ?string $navigationGroup = 'Abiti Speciali';
    protected static ?string $navigationIcon  = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Pronta Misura (Speciali)';
    protected static ?string $modelLabel = 'Abito Speciale Pronta Misura';
    protected static ?string $pluralModelLabel = 'Abiti Speciali Pronta Misura';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return SpecialDressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pronta_misura')) // 👈 tipizzato
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
                    ->sortable()
                    ->color(fn (SpecialDress $record): ?string => optional($record->delivery_date)->isPast() ? 'danger' : null), // 👈 tipizzato

                Tables\Columns\TextColumn::make('total_client_price')
                    ->label('Prezzo Totale')
                    ->money('EUR')
                    ->color('success')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Note')
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn (SpecialDress $record): string => 'https://wa.me/' . preg_replace('/\D+/', '', (string) $record->phone_number)) // 👈 tipizzato
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('consegna_abito')
                    ->label('Consegna abito')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Conferma consegna')
                    ->modalDescription(fn (SpecialDress $record): string => "Confermi la consegna dell'abito di {$record->customer_name}?") // 👈 tipizzato
                    ->action(fn (SpecialDress $record) => $record->update(['status' => 'consegnato'])) // 👈 tipizzato
                    ->successNotificationTitle('Abito consegnato!'),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpecialDressProntaMisura::route('/'),
            'create' => Pages\CreateSpecialDressProntaMisura::route('/create'),
            'edit'   => Pages\EditSpecialDressProntaMisura::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pronta_misura')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $c = static::getModel()::where('status', 'pronta_misura')->count();
        return match (true) {
            $c > 5 => 'danger',
            $c > 2 => 'warning',
            $c > 0 => 'success',
            default => null
        };
    }
}
