<?php

namespace App\Filament\Resources;

use App\Models\SpecialDress;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // 👈 IMPORTANTE
use App\Filament\Resources\SpecialDressConfermatiResource\Pages;
use Filament\Tables\Grouping\Group;

class SpecialDressConfermatiResource extends Resource
{
    protected static ?string $model = SpecialDress::class;

    protected static ?string $navigationGroup = 'Abiti Speciali';
    protected static ?string $navigationIcon  = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Confermati (Speciali)';
    protected static ?string $modelLabel = 'Abito Speciale Confermato';
    protected static ?string $pluralModelLabel = 'Abiti Speciali Confermati';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return SpecialDressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confermato')) // 👈 tipizza
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn (SpecialDress $record): ?string => $record->phone_number) // 👈 tipizza
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('ceremony_type')
                    ->label('Festività')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-') // 👈 tipizza
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
                Tables\Actions\Action::make('tessuto_acquisito')
                    ->label('Tessuto acquisito')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(fn (SpecialDress $record) => $record->update(['status' => 'da_tagliare'])) // 👈 tipizza record (opzionale ma ok)
                    ->successNotificationTitle('Passato a "Da tagliare".'),
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
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('ceremony_type');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpecialDressConfermati::route('/'),
            'create' => Pages\CreateSpecialDressConfermati::route('/create'),
            'edit'   => Pages\EditSpecialDressConfermati::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'confermato')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $c = static::getModel()::where('status', 'confermato')->count();
        return match (true) {
            $c > 5 => 'danger',
            $c > 2 => 'warning',
            $c > 0 => 'success',
            default => null
        };
    }
}
