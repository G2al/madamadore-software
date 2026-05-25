<?php

namespace App\Filament\Widgets;

use App\Models\Dress;
use App\Models\DressFabric;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class FabricSummaryWidget extends BaseWidget
{
    protected static ?string $heading = 'Tessuti automatici dai preventivi (raggruppati per cliente)';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DressFabric::query()
                    ->pendingPurchase()
                    ->with(['dress:id,customer_name,delivery_date', 'supplierRecord:id,name'])
                    ->select([
                        'dress_fabrics.*',
                        DB::raw('(SELECT customer_name FROM dresses WHERE dresses.id = dress_fabrics.dress_id LIMIT 1) as customer_group_name'),
                        DB::raw('(COALESCE(meters,0) * COALESCE(purchase_price,0)) as row_total'),
                    ])
                    ->orderBy(
                        Dress::query()
                            ->select('customer_name')
                            ->whereColumn('dresses.id', 'dress_fabrics.dress_id')
                            ->limit(1)
                    )
                    ->orderBy(
                        Dress::query()
                            ->select('delivery_date')
                            ->whereColumn('dresses.id', 'dress_fabrics.dress_id')
                            ->limit(1)
                    )
                    ->orderBy('name', 'asc')
                    ->orderBy('color_code', 'asc')
            )
            ->defaultGroup('customer_group_name')
            ->groups([
                Group::make('customer_group_name')
                    ->label('Cliente')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (DressFabric $record): string => $record->dress?->customer_name ?: 'Cliente non indicato')
                    ->scopeQueryByKeyUsing(function ($query, string $key) {
                        return $query->whereHas('dress', fn ($dressQuery) => $dressQuery->where('customer_name', $key));
                    })
                    ->getDescriptionFromRecordUsing(function (DressFabric $record): string {
                        $customerName = $record->dress?->customer_name;

                        $totals = DressFabric::query()
                            ->pendingPurchase()
                            ->whereHas('dress', fn ($dressQuery) => $dressQuery->where('customer_name', $customerName))
                            ->selectRaw('COALESCE(SUM(meters),0) as total_meters, COALESCE(SUM(meters * purchase_price),0) as total_cost')
                            ->first();

                        $meters = number_format((float) ($totals->total_meters ?? 0), 2, ',', '.');
                        $cost = number_format((float) ($totals->total_cost ?? 0), 2, ',', '.');

                        return "Metri: {$meters} mt - Totale: € {$cost}";
                    }),
            ])
            ->groupingSettingsHidden()
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->square()
                    ->height(60)
                    ->circular(false),

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
                        if (! $record->dress?->delivery_date) {
                            return 'gray';
                        }

                        $deliveryDate = Carbon::parse($record->dress->delivery_date);
                        $now = Carbon::now();
                        $daysUntilDelivery = $now->diffInDays($deliveryDate, false);

                        if ($daysUntilDelivery < 0) {
                            return 'danger';
                        }

                        if ($daysUntilDelivery <= 7) {
                            return 'warning';
                        }

                        if ($daysUntilDelivery <= 14) {
                            return 'info';
                        }

                        return 'success';
                    })
                    ->formatStateUsing(function ($record) {
                        if (! $record->dress?->delivery_date) {
                            return 'Non definita';
                        }

                        $deliveryDate = Carbon::parse($record->dress->delivery_date);
                        $now = Carbon::now();
                        $daysUntilDelivery = $now->diffInDays($deliveryDate, false);
                        $formatted = $deliveryDate->format('d/m/Y');

                        if ($daysUntilDelivery < 0) {
                            return $formatted . ' (SCADUTO)';
                        }

                        if ($daysUntilDelivery <= 7) {
                            return $formatted . ' (URGENTE)';
                        }

                        if ($daysUntilDelivery <= 14) {
                            return $formatted . ' (PROSSIMO)';
                        }

                        return $formatted;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tessuto')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('color_code')
                    ->label('Codice Colore')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('meters')
                    ->label('Quantita')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.') . ' mt')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Fornitore')
                    ->state(fn (DressFabric $record): string => $record->supplierRecord?->name ?? $record->supplier ?? '-')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($query) use ($search) {
                            $query->where('supplier', 'like', "%{$search}%")
                                ->orWhereHas('supplierRecord', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"));
                        });
                    })
                    ->toggleable(),

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
                Tables\Columns\TextColumn::make('purchase_label')
                    ->label('Data Acquisto')
                    ->state('Non saldato')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('dress.customer_name')
                    ->label('Cliente')
                    ->options(fn () => DressFabric::query()
                        ->pendingPurchase()
                        ->with('dress:id,customer_name')
                        ->get()
                        ->pluck('dress.customer_name', 'dress.customer_name')
                        ->unique()
                        ->sort()
                        ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! isset($data['value']) || $data['value'] === '') {
                            return $query;
                        }

                        return $query->whereHas('dress', fn ($dressQuery) => $dressQuery->where('customer_name', $data['value']));
                    }),

                SelectFilter::make('name')
                    ->label('Tessuto')
                    ->options(fn () => DressFabric::query()
                        ->pendingPurchase()
                        ->whereNotNull('name')
                        ->orderBy('name')
                        ->pluck('name', 'name')
                        ->unique()
                        ->toArray()
                    ),

                SelectFilter::make('color_code')
                    ->label('Codice Colore')
                    ->options(fn () => DressFabric::query()
                        ->pendingPurchase()
                        ->whereNotNull('color_code')
                        ->orderBy('color_code')
                        ->pluck('color_code', 'color_code')
                        ->unique()
                        ->toArray()
                    ),

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
                        if (! isset($data['value']) || $data['value'] === '') {
                            return $query;
                        }

                        $now = Carbon::now();

                        return $query->whereHas('dress', function ($dressQuery) use ($data, $now) {
                            switch ($data['value']) {
                                case 'scaduto':
                                    return $dressQuery->whereNotNull('delivery_date')
                                        ->where('delivery_date', '<', $now->toDateString());

                                case 'urgente':
                                    return $dressQuery->whereNotNull('delivery_date')
                                        ->whereBetween('delivery_date', [
                                            $now->toDateString(),
                                            $now->copy()->addDays(7)->toDateString(),
                                        ]);

                                case 'prossimo':
                                    return $dressQuery->whereNotNull('delivery_date')
                                        ->whereBetween('delivery_date', [
                                            $now->copy()->addDays(8)->toDateString(),
                                            $now->copy()->addDays(14)->toDateString(),
                                        ]);

                                case 'normale':
                                    return $dressQuery->whereNotNull('delivery_date')
                                        ->where('delivery_date', '>', $now->copy()->addDays(14)->toDateString());

                                case 'senza_data':
                                    return $dressQuery->whereNull('delivery_date');
                            }

                            return $dressQuery;
                        });
                    }),

                Filter::make('questa_settimana')
                    ->label('Questa settimana')
                    ->query(function ($query) {
                        $startOfWeek = Carbon::now()->startOfWeek();
                        $endOfWeek = Carbon::now()->endOfWeek();

                        return $query->whereHas('dress', function ($dressQuery) use ($startOfWeek, $endOfWeek) {
                            $dressQuery->whereBetween('delivery_date', [
                                $startOfWeek->toDateString(),
                                $endOfWeek->toDateString(),
                            ]);
                        });
                    }),

                Filter::make('prossima_settimana')
                    ->label('Prossima settimana')
                    ->query(function ($query) {
                        $startOfNextWeek = Carbon::now()->addWeek()->startOfWeek();
                        $endOfNextWeek = Carbon::now()->addWeek()->endOfWeek();

                        return $query->whereHas('dress', function ($dressQuery) use ($startOfNextWeek, $endOfNextWeek) {
                            $dressQuery->whereBetween('delivery_date', [
                                $startOfNextWeek->toDateString(),
                                $endOfNextWeek->toDateString(),
                            ]);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifica')
                    ->modalHeading(fn (DressFabric $record): string => 'Modifica tessuto - ' . ($record->dress?->customer_name ?? 'Cliente non indicato'))
                    ->form([
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto')
                            ->disk('public')
                            ->directory('dress-fabrics')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->previewable()
                            ->downloadable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('type')
                            ->label('Tipo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('color_code')
                            ->label('Codice Colore')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meters')
                            ->label('Quantita')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('mt'),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Prezzo acquisto')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('EUR'),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Fornitore')
                            ->relationship('supplierRecord', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Supplier $record): string => $record->name),
                    ]),

                Tables\Actions\Action::make('pdf_codice')
                    ->label('PDF Gruppo')
                    ->tooltip('Scarica PDF per questo codice colore')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn (DressFabric $record) => route('pdf.fabrics', ['color' => $record->color_code, 'download' => 1]))
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('stampa_pdf')
                    ->label('Stampa Lista della Spesa Unica')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn () => route('pdf.fabrics'))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
