<?php

namespace App\Filament\Widgets;

use App\Models\DressFabric;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FabricSummaryWidget extends BaseWidget
{
    protected static ?string $heading = 'Tessuti (raggruppati per tipo)';
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DressFabric::query()
                    ->with(['dress:id,customer_name,status,delivery_date'])
                    ->select([
                        'dress_fabrics.*',
                        DB::raw('(COALESCE(meters,0) * COALESCE(purchase_price,0)) as row_total'),
                    ])
                    ->whereHas('dress', fn ($q) => $q->where('status', 'confermato'))
                    ->orderBy('name', 'asc')
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
            ->groupingSettingsHidden() // 👈 Questa riga nasconde i controlli di raggruppamento
            ->columns([
                Tables\Columns\TextColumn::make('dress.customer_name')
                    ->label('Cliente')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dress.delivery_date')
                    ->label('Consegna')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->dress?->delivery_date) {
                            return 'gray';
                        }
                        
                        $deliveryDate = Carbon::parse($record->dress->delivery_date);
                        $now = Carbon::now();
                        $daysUntilDelivery = $now->diffInDays($deliveryDate, false);
                        
                        if ($daysUntilDelivery < 0) {
                            return 'danger';
                        } elseif ($daysUntilDelivery <= 7) {
                            return 'warning';
                        } elseif ($daysUntilDelivery <= 14) {
                            return 'info';
                        } else {
                            return 'success';
                        }
                    })
                    ->formatStateUsing(function ($record) {
                        if (!$record->dress?->delivery_date) {
                            return 'Non definita';
                        }
                        
                        $deliveryDate = Carbon::parse($record->dress->delivery_date);
                        $now = Carbon::now();
                        $daysUntilDelivery = $now->diffInDays($deliveryDate, false);
                        
                        $formatted = $deliveryDate->format('d/m/Y');
                        
                        if ($daysUntilDelivery < 0) {
                            return $formatted . ' (SCADUTO)';
                        } elseif ($daysUntilDelivery <= 7) {
                            return $formatted . ' (URGENTE)';
                        } elseif ($daysUntilDelivery <= 14) {
                            return $formatted . ' (PROSSIMO)';
                        } else {
                            return $formatted;
                        }
                    })
                    ->sortable(),

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
            ->filters([
                SelectFilter::make('urgenza')
                    ->label('Filtro Urgenza')
                    ->options([
                        'scaduto' => 'Scaduto',
                        'urgente' => 'Urgente (entro 7 giorni)',
                        'prossimo' => 'Prossimo (entro 14 giorni)',
                        'normale' => 'Normale (oltre 14 giorni)',
                        'senza_data' => 'Senza data di consegna',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === '') {
                            return $query;
                        }

                        $now = Carbon::now();
                        
                        return $query->whereHas('dress', function ($q) use ($data, $now) {
                            switch ($data['value']) {
                                case 'scaduto':
                                    return $q->whereNotNull('delivery_date')
                                             ->where('delivery_date', '<', $now->toDateString());
                                
                                case 'urgente':
                                    return $q->whereNotNull('delivery_date')
                                             ->whereBetween('delivery_date', [
                                                 $now->toDateString(),
                                                 $now->copy()->addDays(7)->toDateString()
                                             ]);
                                
                                case 'prossimo':
                                    return $q->whereNotNull('delivery_date')
                                             ->whereBetween('delivery_date', [
                                                 $now->copy()->addDays(8)->toDateString(),
                                                 $now->copy()->addDays(14)->toDateString()
                                             ]);
                                
                                case 'normale':
                                    return $q->whereNotNull('delivery_date')
                                             ->where('delivery_date', '>', $now->copy()->addDays(14)->toDateString());
                                
                                case 'senza_data':
                                    return $q->whereNull('delivery_date');
                            }
                        });
                    }),

                Filter::make('questa_settimana')
                    ->label('Questa settimana')
                    ->query(function ($query) {
                        $startOfWeek = Carbon::now()->startOfWeek();
                        $endOfWeek = Carbon::now()->endOfWeek();
                        
                        return $query->whereHas('dress', function ($q) use ($startOfWeek, $endOfWeek) {
                            $q->whereBetween('delivery_date', [
                                $startOfWeek->toDateString(),
                                $endOfWeek->toDateString()
                            ]);
                        });
                    }),

                Filter::make('prossima_settimana')
                    ->label('Prossima settimana')
                    ->query(function ($query) {
                        $startOfNextWeek = Carbon::now()->addWeek()->startOfWeek();
                        $endOfNextWeek = Carbon::now()->addWeek()->endOfWeek();
                        
                        return $query->whereHas('dress', function ($q) use ($startOfNextWeek, $endOfNextWeek) {
                            $q->whereBetween('delivery_date', [
                                $startOfNextWeek->toDateString(),
                                $endOfNextWeek->toDateString()
                            ]);
                        });
                    }),
            ])
            ->paginated(false);
    }
}