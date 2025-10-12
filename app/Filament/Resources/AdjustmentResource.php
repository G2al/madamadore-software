<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdjustmentResource\Pages;
use App\Filament\Resources\AdjustmentResource\Concerns\HasAdjustmentFormSections;
use App\Filament\Resources\AdjustmentResource\Concerns\HasAdjustmentTableDefinition;
use App\Models\Adjustment;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use InvalidArgumentException;

class AdjustmentResource extends Resource
{
    use HasAdjustmentFormSections;
    use HasAdjustmentTableDefinition;

    protected static ?string $model = Adjustment::class;

    // Configurazione navigazione principale
    protected static ?string $navigationGroup = 'Aggiusti';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Tutti gli Aggiusti';
    protected static ?string $modelLabel = 'Aggiusto';
    protected static ?string $pluralModelLabel = 'Aggiusti';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            self::clientSection(),
            self::paymentSection(),
            self::expenseSection(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return self::buildTable($table);
    }

    protected static function updateCalculations(Set $set, Get $get): void
    {
        $price = (float) ($get('client_price') ?? 0);
        $deposit = $get('deposit');

        // se è null, consideralo 0
        $deposit = is_null($deposit) ? 0.0 : (float) $deposit;

        // sicurezza: mai minore di 0, mai maggiore del prezzo
        if ($deposit < 0) {
            $deposit = 0;
        }
        if ($deposit > $price) {
            $deposit = $price;
        }

        $set('total', number_format($price, 2, '.', ''));
        $set('remaining', number_format($price - $deposit, 2, '.', ''));
        $set('profit', number_format($price, 2, '.', '')); // guadagno = prezzo
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdjustments::route('/'),
            'create' => Pages\CreateAdjustment::route('/create'),
            'edit'   => Pages\EditAdjustment::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio nel menu
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
        $count > 0 => 'primary',
        default => null
    };
}
}