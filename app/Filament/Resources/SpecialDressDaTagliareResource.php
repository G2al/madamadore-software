<?php

namespace App\Filament\Resources;

use App\Models\SpecialDress;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // 👈
use App\Filament\Resources\SpecialDressDaTagliareResource\Pages;
use Filament\Tables\Grouping\Group;

class SpecialDressDaTagliareResource extends Resource
{
    protected static ?string $model = SpecialDress::class;

    protected static ?string $navigationGroup = 'Abiti Speciali';
    protected static ?string $navigationIcon  = 'heroicon-o-scissors';
    protected static ?string $navigationLabel = 'Da Tagliare (Speciali)';
    protected static ?string $modelLabel = 'Abito Speciale da Tagliare';
    protected static ?string $pluralModelLabel = 'Abiti Speciali da Tagliare';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return SpecialDressResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'da_tagliare')) // 👈 tipizzato
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

                Tables\Columns\TextColumn::make('character')
                    ->label('Personaggio/Maschera')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

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
            ])
            ->actions([
                Tables\Actions\Action::make('visualizza_misure')
    ->label('Visualizza misure')
    ->icon('heroicon-o-user')
    ->color('info')
    ->slideOver()
    ->modalHeading(fn (SpecialDress $record): string => "Misure di {$record->customer_name}")
    ->infolist(function () {
        $schema = [];

        // Campi base ordinati
        foreach (\App\Models\SpecialDressMeasurement::ORDERED_MEASURES as $field => $label) {
            $suffix = $field === 'inclinazione_spalle' ? '°' : ' cm';
            $schema[] = \Filament\Infolists\Components\TextEntry::make("measurements.{$field}")
                ->label($label)
                ->suffix($suffix)
                ->placeholder('—');
        }

        // Sezione misure personalizzate (array JSON su campo custom_measurements)
        $schema[] = \Filament\Infolists\Components\Section::make('Misure Personalizzate')
            ->schema([
                \Filament\Infolists\Components\RepeatableEntry::make('custom_measurements')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('label')->label('Nome')->weight('bold'),
                        \Filament\Infolists\Components\TextEntry::make('value')->label('Valore')->suffix(' cm'),
                        \Filament\Infolists\Components\TextEntry::make('notes')->label('Note'),
                    ])
                    ->columns(3),
            ])
            ->visible(fn (SpecialDress $record): bool =>
                is_array($record->custom_measurements) && count($record->custom_measurements) > 0
            );

        return [
            \Filament\Infolists\Components\Section::make('Misure')
                ->schema($schema)
                ->columns(3),
        ];
    })
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Chiudi'),


                Tables\Actions\Action::make('pronto_misure')
                    ->label('Pronto per la misura')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (SpecialDress $record) => $record->update(['status' => 'pronta_misura'])) // 👈 tipizzato
                    ->successNotificationTitle('Passato a "Pronta misura".'),

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
            'index'  => Pages\ListSpecialDressDaTagliare::route('/'),
            'create' => Pages\CreateSpecialDressDaTagliare::route('/create'),
            'edit'   => Pages\EditSpecialDressDaTagliare::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'da_tagliare')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $c = static::getModel()::where('status', 'da_tagliare')->count();
        return match (true) {
            $c > 5 => 'danger',
            $c > 2 => 'warning',
            $c > 0 => 'primary',
            default => null
        };
    }
}
