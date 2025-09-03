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
use Illuminate\Support\Facades\Storage;

class DressResource extends Resource
{
    protected static ?string $model = Dress::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Abiti';
    protected static ?string $modelLabel = 'Abito';
    protected static ?string $pluralModelLabel = 'Abiti';

    public const STATUS_LABELS = [
        'in_attesa_acconto' => 'In Attesa Acconto',
        'confermato'        => 'Confermato',
        'in_lavorazione'    => 'In Lavorazione',
        'consegnato'        => 'Consegnato',
    ];

    public const STATUS_COLORS = [
        'in_attesa_acconto' => 'warning',
        'confermato'        => 'info',
        'in_lavorazione'    => 'primary',
        'consegnato'        => 'success',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SEZIONE 1 - DATI CONTATTO
                Forms\Components\Section::make('Dati Contatto')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nome e Cognome Cliente')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Numero di Cellulare')
                            ->tel()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('ceremony_date')
                            ->label('Data della Cerimonia')
                            ->required(),

                        Forms\Components\Select::make('ceremony_type')
                            ->label('Tipologia della Cerimonia')
                            ->options([
                                'matrimonio'   => 'Matrimonio',
                                'battesimo'    => 'Battesimo',
                                'comunione'    => 'Comunione',
                                'cresima'      => 'Cresima',
                                'festa_18anni' => 'Festa 18 Anni',
                                'laurea'       => 'Laurea',
                                'altro'        => 'Altro',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('ceremony_holder')
                            ->label('Intestatario della Cerimonia')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Data di Consegna Prevista')
                            ->required(),
                    ])
                    ->columns(2),

                // Upload Immagini
                Forms\Components\Section::make('Immagini Abito')
                    ->schema([
                        Forms\Components\FileUpload::make('sketch_image')
                            ->label('Bozza')->image()->disk('public')->directory('dress-sketches')->visibility('public')
                            ->acceptedFileTypes(['image/jpeg','image/png','image/gif'])
                            ->downloadable(),

                        Forms\Components\FileUpload::make('final_image')
                            ->label('Definitivo')->image()->disk('public')->directory('dress-finals')->visibility('public')
                            ->acceptedFileTypes(['image/jpeg','image/png','image/gif'])
                            ->downloadable(),
                    ])
                    ->columns(2),

                // Note
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->rows(4)
                    ->columnSpanFull(),

                // SEZIONE 2 - PREVENTIVO
                Forms\Components\Section::make('Preventivo')
                    ->schema([
                        Forms\Components\TextInput::make('estimated_time')
                            ->label('Tempo Stimato Manifattura')
                            ->placeholder('es: 15 giorni oppure 120 ore')
                            ->maxLength(255),

                        // Tessuti - Repeater
                        Forms\Components\Repeater::make('fabrics')
                            ->label('Tessuti')
                            ->relationship('fabrics')
                            // niente ->live() qui: evitiamo trigger continui
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateCalculations($set, $get);
                            })
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome Tessuto')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('type')
                                    ->label('Tipologia')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('meters')
                                    ->label('Metratura')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('mt')
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::updateCalculations($set, $get);
                                    }),

                                Forms\Components\TextInput::make('purchase_price')
                                    ->label('Prezzo Acquisto')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::updateCalculations($set, $get);
                                    }),

                                Forms\Components\TextInput::make('client_price')
                                    ->label('Prezzo Cliente')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::updateCalculations($set, $get);
                                    }),

                                Forms\Components\TextInput::make('color_code')
                                    ->label('Codice Colore')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('supplier')
                                    ->label('Fornitore')
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->addActionLabel('Aggiungi Tessuto'),

                        // Extra Aggiuntivi - Repeater
                        Forms\Components\Repeater::make('extras')
                            ->label('Extra Aggiuntivi')
                            ->relationship('extras')
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateCalculations($set, $get);
                            })
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->label('Descrizione')
                                    ->maxLength(255)
                                    ->placeholder('es: Cucitura a cuore'),

                                Forms\Components\TextInput::make('cost')
                                    ->label('Costo')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::updateCalculations($set, $get);
                                    }),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->addActionLabel('Aggiungi Extra'),
                    ])
                    ->columnSpanFull(),

                // SEZIONE 3 - MISURE
                Forms\Components\Section::make('Misure')
                    ->schema([
                        Forms\Components\Repeater::make('measurements')
                            ->label('Misure Cliente')
                            ->relationship('measurements')
                            ->schema([
                                Forms\Components\TextInput::make('spalle')->label('Spalle')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('torace')->label('Torace')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('sotto_seno')->label('Sotto Seno')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('vita')->label('Vita')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('fianchi')->label('Fianchi')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('lunghezza_busto')->label('Lunghezza Busto')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('lunghezza_manica')->label('Lunghezza Manica')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('circonferenza_braccio')->label('Circonferenza Braccio')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('circonferenza_polso')->label('Circonferenza Polso')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('altezza_totale')->label('Altezza Totale')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('lunghezza_abito')->label('Lunghezza Abito')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('lunghezza_gonna')->label('Lunghezza Gonna')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('circonferenza_collo')->label('Circonferenza Collo')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('larghezza_schiena')->label('Larghezza Schiena')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('altezza_seno')->label('Altezza Seno')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('distanza_seni')->label('Distanza Seni')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('circonferenza_coscia')->label('Circonferenza Coscia')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('lunghezza_cavallo')->label('Lunghezza Cavallo')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('altezza_ginocchio')->label('Altezza Ginocchio')->numeric()->step(0.1)->suffix('cm'),
                                Forms\Components\TextInput::make('circonferenza_caviglia')->label('Circonferenza Caviglia')->numeric()->step(0.1)->suffix('cm'),
                            ])
                            ->columns(4)
                            ->itemLabel('Misure')
                            ->maxItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Aggiungi Misure'),
                    ])
                    ->columnSpanFull(),

                // SEZIONE FINALE - CALCOLI E STATO
                Forms\Components\Section::make('Totali e Stato')
                    ->schema([
                        // Calcoli automatici
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('total_purchase_cost')
                                    ->label('Costo Totale (per te)')
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('total_client_price')
                                    ->label('Prezzo Cliente')
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('total_profit')
                                    ->label('Guadagno')
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('remaining_balance')
                                    ->label('Rimanente da Pagare')
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        // Campi editabili
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('deposit')
                                    ->label('Acconto')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->default(0)
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::updateCalculations($set, $get);
                                    }),

                                Forms\Components\Select::make('status')
                                    ->label('Stato')
                                    ->options(self::STATUS_LABELS)
                                    ->default('in_attesa_acconto'),
                                ]),
                    ])
                    ->columnSpanFull(),

                // Trigger robusto all'apertura del form (edit/create)
                Forms\Components\Placeholder::make('_boot_calc')
                    ->label('')
                    ->content('')
                    ->extraAttributes(['style' => 'display:none'])
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Set $set, Get $get) {
                        self::updateCalculations($set, $get);
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_info')
                    ->label('Cliente')
                    ->html()
                    ->searchable(['customer_name', 'phone_number'])
                    ->getStateUsing(fn ($record) =>
                        "<strong>{$record->customer_name}</strong><br>
                        <small class='text-gray-500'>{$record->phone_number}</small>"
                    ),

                Tables\Columns\TextColumn::make('ceremony_info')
                    ->label('Cerimonia')
                    ->html()
                    ->getStateUsing(fn ($record) =>
                        "<strong>{$record->ceremony_date?->format('d/m/Y')}</strong><br>
                        <small class='text-gray-500'>{$record->ceremony_type}</small>"
                    ),

                Tables\Columns\TextColumn::make('status')
    ->label('Stato')
    ->badge() // mantiene il pill rotondo
    ->formatStateUsing(fn (?string $state) =>
        self::STATUS_LABELS[$state]
        ?? ($state ? ucwords(str_replace('_', ' ', $state)) : '-')
    )
    ->color(fn (?string $state) => self::STATUS_COLORS[$state] ?? 'gray'),


                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Consegna')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->delivery_date->isPast() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('deposit')
                    ->label('Acconto')
                    ->money('EUR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Stato')
                    ->options(self::STATUS_LABELS),

                Tables\Filters\SelectFilter::make('ceremony_type')
                    ->label('Tipo Cerimonia')
                    ->options([
                        'matrimonio'   => 'Matrimonio',
                        'battesimo'    => 'Battesimo',
                        'comunione'    => 'Comunione',
                        'cresima'      => 'Cresima',
                        'festa_18anni' => 'Festa 18 Anni',
                        'laurea'       => 'Laurea',
                        'altro'        => 'Altro',
                    ]),

                Tables\Filters\Filter::make('delivery_date')
                    ->form([
                        Forms\Components\DatePicker::make('delivery_from')->label('Consegna da'),
                        Forms\Components\DatePicker::make('delivery_until')->label('Consegna fino a'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['delivery_from'] ?? null, fn ($q, $date) => $q->whereDate('delivery_date', '>=', $date))
                            ->when($data['delivery_until'] ?? null, fn ($q, $date) => $q->whereDate('delivery_date', '<=', $date));
                    }),
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
        // Calcola costi tessuti
        $fabrics = $get('fabrics') ?? [];
        $totalPurchaseCost = 0.0;
        $totalFabricClientPrice = 0.0;

        foreach ($fabrics as $fabric) {
            $meters        = (float) ($fabric['meters'] ?? 0);
            $purchasePrice = (float) ($fabric['purchase_price'] ?? 0);
            $clientPrice   = (float) ($fabric['client_price'] ?? 0);

            $totalPurchaseCost      += $meters * $purchasePrice;
            $totalFabricClientPrice += $meters * $clientPrice;
        }

        // Calcola extra
        $extras = $get('extras') ?? [];
        $totalExtras = 0.0;
        foreach ($extras as $extra) {
            $totalExtras += (float) ($extra['cost'] ?? 0);
        }

        // Totali
        $totalClientPrice = $totalFabricClientPrice + $totalExtras;
        $profit           = $totalClientPrice - $totalPurchaseCost;
        $deposit          = (float) ($get('deposit') ?? 0);
        $remaining        = $totalClientPrice - $deposit;

        // Aggiorna campi (solo visualizzazione)
        $set('total_purchase_cost', number_format($totalPurchaseCost, 2, '.', ''));
        $set('total_client_price', number_format($totalClientPrice, 2, '.', ''));
        $set('total_profit', number_format($profit, 2, '.', ''));
        $set('remaining_balance', number_format($remaining, 2, '.', ''));
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
