<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DressResource\Pages;
use App\Models\Dress;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Filament\Resources\DressResource\Concerns\HasDressFormSections;
use App\Filament\Resources\DressResource\Concerns\HasDressTableDefinition;
use App\Services\DressCalculator;

class DressResource extends Resource
{
    use HasDressFormSections;
    use HasDressTableDefinition;

    protected static ?string $model = Dress::class;
    
    // Configurazione navigazione principale
    protected static ?string $navigationGroup = 'Abiti';
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Tutti gli Abiti';
    protected static ?string $modelLabel = 'Abito';
    protected static ?string $pluralModelLabel = 'Abiti';
    protected static ?int $navigationSort = 1;

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
            self::expenseSection(),
            self::quoteSection(),
            self::measurementsSection(),
            self::corsetsSection(),
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
        $fabrics = $get('fabrics') ?? [];
        $extras  = $get('extras') ?? [];
        $deposit = (float) ($get('deposit') ?? 0);
        $manufacturingPrice = (float) ($get('manufacturing_price') ?? 0);
        $useManual = (bool) $get('use_manual_price');
        $manualPrice = (float) ($get('manual_client_price') ?? 0);

        if (!$useManual && empty($fabrics) && empty($extras) && $deposit === 0.0 && $manufacturingPrice === 0.0) {
            return;
        }

        $results = \App\Services\DressCalculator::calculate($fabrics, $extras, $deposit, $manufacturingPrice);

        if ($useManual && $manualPrice > 0) {
            $results['total_client_price'] = $manualPrice;
            $results['total_profit'] = $manualPrice - $results['total_purchase_cost'];
            $results['remaining'] = $manualPrice - $deposit;
        }

        foreach ($results as $field => $value) {
            if (!is_numeric($value)) {
                continue;
            }
            $set($field, number_format((float) $value, 2, '.', ''));
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDresses::route('/'),
            'create' => Pages\CreateDress::route('/create'),
            'edit'   => Pages\EditDress::route('/{record}/edit'),
        ];
    }

    // Badge con conteggio totale
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