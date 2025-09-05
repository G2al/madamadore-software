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
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Abiti';
    protected static ?string $modelLabel = 'Abito';
    protected static ?string $pluralModelLabel = 'Abiti';

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
            self::quoteSection(),
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
        $fabrics = $get('fabrics') ?? [];
        $extras = $get('extras') ?? [];
        $deposit = (float) ($get('deposit') ?? 0);

        // 🚦 Early exit: se non ci sono dati, non fare calcoli
        if (empty($fabrics) && empty($extras) && $deposit === 0.0) {
            return;
        }

        $results = DressCalculator::calculate($fabrics, $extras, $deposit);

        foreach ($results as $field => $value) {
            // ✅ Fail-fast: assicuriamoci che sia numerico
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
}
