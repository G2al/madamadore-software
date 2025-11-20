<?php

namespace App\Filament\Resources;

use App\Models\SpecialDress;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SpecialDressInAttesaAccontoResource\Pages;
use Filament\Tables\Grouping\Group;

class SpecialDressInAttesaAccontoResource extends Resource
{
    protected static ?string $model = SpecialDress::class;

    // Navigazione
    protected static ?string $navigationGroup = 'Abiti Speciali';
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'In Attesa Acconto (Speciali)';
    protected static ?string $modelLabel = 'Abito Speciale in Attesa Acconto';
    protected static ?string $pluralModelLabel = 'Abiti Speciali in Attesa Acconto';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return SpecialDressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_attesa_acconto'))
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn (SpecialDress $record): ?string => $record->phone_number)
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('ceremony_type')
                    ->label('Festività')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-')
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('acconto_ricevuto')
                    ->label('Acconto ricevuto')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (SpecialDress $record) => $record->update(['status' => 'confermato']))
                    ->successNotificationTitle('Abito confermato!'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(false),
                ]),
            ])
            ->groups([
                Group::make('ceremony_type')
                    ->label('Festività')
                    ->collapsible(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('ceremony_type');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpecialDressInAttesaAcconto::route('/'),
            'create' => Pages\CreateSpecialDressInAttesaAcconto::route('/create'),
            'edit'   => Pages\EditSpecialDressInAttesaAcconto::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_attesa_acconto')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $c = static::getModel()::where('status', 'in_attesa_acconto')->count();
        return match (true) {
            $c > 5 => 'danger',
            $c > 2 => 'warning',
            $c > 0 => 'primary',
            default => null
        };
    }
}
