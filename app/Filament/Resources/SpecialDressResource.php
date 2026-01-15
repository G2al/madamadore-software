<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialDressResource\Pages;
use App\Filament\Resources\SpecialDressResource\Concerns\HasSpecialDressFormSections;
use App\Filament\Resources\SpecialDressResource\Concerns\HasSpecialDressTableDefinition;
use App\Models\SpecialDress;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SpecialDressResource extends Resource
{
    use HasSpecialDressFormSections;
    use HasSpecialDressTableDefinition;

    protected static ?string $model = SpecialDress::class;

    protected static ?string $navigationGroup = 'Abiti Speciali';
    protected static ?string $navigationLabel = 'Tutti gli Abiti Speciali';
    protected static ?string $modelLabel      = 'Abito Speciale';
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $pluralModelLabel= 'Abiti Speciali';
    protected static ?int    $navigationSort  = 1;

    public static function getStatusLabels(): array
    {
        return collect(config('dress.statuses'))
            ->mapWithKeys(fn ($s, $key) => [$key => $s['label']])
            ->toArray();
    }

    public static function getStatusColors(): array
    {
        return collect(config('dress.statuses'))
            ->mapWithKeys(fn ($s, $key) => [$key => $s['color']])
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            self::contactSection(),
            self::imagesSection(),
            self::notesSection(),
            self::measurementsSection(),
            self::totalsSection(),
            self::bootCalcPlaceholder(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return self::buildTable($table);
    }

    protected static function updateCalculations(Set $set, Get $get): void
    {
        $price   = (float) ($get('total_client_price') ?? 0);
        $deposit = (float) ($get('deposit') ?? 0);
        $remaining = max($price - $deposit, 0);

        $set('remaining', number_format($remaining, 2, '.', ''));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpecialDresses::route('/'),
            'create' => Pages\CreateSpecialDress::route('/create'),
            'edit'   => Pages\EditSpecialDress::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        return match (true) {
            $count > 20 => 'danger',
            $count > 10 => 'warning',
            $count > 0  => 'primary',
            default     => null
        };
    }
}
