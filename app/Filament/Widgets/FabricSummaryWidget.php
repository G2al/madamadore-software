<?php

namespace App\Filament\Widgets;

use App\Models\DressFabric;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class FabricSummaryWidget extends BaseWidget
{
    protected static ?string $heading = 'Tessuti (raggruppati per tipo)';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DressFabric::query()
                    ->with(['dress:id,customer_name,status'])
                    ->select([
                        'dress_fabrics.*',
                        DB::raw('(COALESCE(meters,0) * COALESCE(purchase_price,0)) as row_total'),
                    ])
                    ->whereHas('dress', fn ($q) => $q->where('status', 'confermato'))
                    ->orderBy('name', 'asc') // 👈 default: alfabetico crescente
            )
            ->defaultGroup('name')
            ->groups([
                Group::make('name')
                    ->label('Tessuto')
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(function (DressFabric $record): string {
                        $totals = DressFabric::query()
                            ->where('name', $record->name)
                            ->whereHas('dress', fn ($q) => $q->where('status', 'confermato'))
                            ->selectRaw('COALESCE(SUM(meters),0) as total_meters, COALESCE(SUM(meters * purchase_price),0) as total_cost')
                            ->first();

                        $m = number_format((float) ($totals->total_meters ?? 0), 2, ',', '.');
                        $c = number_format((float) ($totals->total_cost ?? 0), 2, ',', '.');

                        return "Metri: {$m} mt — Totale: € {$c}";
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('dress.customer_name')
                    ->label('Cliente')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tessuto')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('meters')
                    ->label('Metri')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.') . ' mt')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('€/m')
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => '€ ' . number_format((float) $state, 2, ',', '.'))
                    ->alignRight(),

                Tables\Columns\TextColumn::make('row_total')
                    ->label('Subtotale')
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => '€ ' . number_format((float) $state, 2, ',', '.'))
                    ->alignRight(),
            ])
            ->paginated(false);
    }
}
