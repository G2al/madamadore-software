<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DressResource\Pages;
use App\Models\Dress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\DressResource\Concerns\HasDressFormSections;
use App\Services\DressCalculator;
use App\Filament\Filters\StatusFilter;
use App\Filament\Filters\CeremonyTypeFilter;
use App\Filament\Filters\DeliveryDateFilter;

class DressResource extends Resource
{
    use HasDressFormSections;

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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_info')
                    ->label('Cliente')
                    ->html()
                    ->searchable(['customer_name', 'phone_number']),

                Tables\Columns\TextColumn::make('ceremony_info')
                    ->label('Cerimonia')
                    ->html(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::getStatusLabels()[$state] ?? '-')
                    ->color(fn (?string $state) => self::getStatusColors()[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Consegna')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->delivery_date->isPast() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('deposit')
                    ->label('Acconto')
                    ->money('EUR'),
            ])
            ->filters([
                StatusFilter::make(),
                CeremonyTypeFilter::make(),
                DeliveryDateFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function updateCalculations(Set $set, Get $get): void
    {
        $results = DressCalculator::calculate(
            $get('fabrics') ?? [],
            $get('extras') ?? [],
            (float) ($get('deposit') ?? 0)
        );

        foreach ($results as $field => $value) {
            $set($field, number_format($value, 2, '.', ''));
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
