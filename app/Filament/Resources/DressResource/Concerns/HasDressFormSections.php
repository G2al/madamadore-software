<?php

namespace App\Filament\Resources\DressResource\Concerns;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\DressMeasurement;

trait HasDressFormSections
{
    // --- SEZIONE 1: Dati Contatto ---
    private static function contactSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Dati Contatto')
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nome e Cognome Cliente')
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Numero di Cellulare')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('ceremony_date')
                    ->label('Data della Cerimonia')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection(),

                Forms\Components\TextInput::make('ceremony_type')
                    ->label('Tipologia della Cerimonia')
                    ->placeholder('es: Matrimonio, Battesimo, Comunione...')
                    ->datalist([
                        'Matrimonio',
                        'Battesimo', 
                        'Comunione',
                        'Cresima',
                        'Festa 18 Anni',
                        'Laurea',
                        'Altro'
                    ])
                    ->maxLength(255),

                Forms\Components\TextInput::make('ceremony_holder')
                    ->label('Intestatario della Cerimonia')
                    ->maxLength(255),
            Forms\Components\DatePicker::make('delivery_date')
                ->label('Data di Consegna Prevista')
                ->native(false) // flatpickr
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection(),
        ])
        ->columns(2);
    }

    // --- SEZIONE 2: Immagini Abito ---
    private static function imagesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Immagini Abito')
            ->schema([
                Forms\Components\FileUpload::make('sketch_image')
                    ->label('Bozza')->image()->disk('public')->directory('dress-sketches')->visibility('public')
                    ->acceptedFileTypes(['image/*'])
                    ->downloadable(),

                Forms\Components\FileUpload::make('final_image')
                    ->label('Definitivo')->image()->disk('public')->directory('dress-finals')->visibility('public')
                    ->acceptedFileTypes(['image/*'])
                    ->downloadable(),
            ])
            ->columns(2);
    }

    // --- SEZIONE 3: Note ---
    private static function notesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Note')
            ->schema([
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->rows(4),
            ])
            ->columnSpanFull();
    }

    // --- SEZIONE 4: Preventivo (Tessuti + Extra) ---
    private static function quoteSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Preventivo')
            ->schema([
                Forms\Components\TextInput::make('estimated_time')
                    ->label('Tempo Stimato Manifattura')
                    ->placeholder('es: 15 giorni oppure 120 ore')
                    ->maxLength(255),

                Forms\Components\TextInput::make('manufacturing_price')  // <- NUOVO CAMPO
                    ->label('Prezzo Manifattura')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€')
                    ->default(0)
                    ->live(debounce: 300)
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),
// Tessuti
Forms\Components\Repeater::make('fabrics')
    ->label('Tessuti')
    ->relationship('fabrics')
    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get))
    ->schema([

        // 👇 Select collegato all’inventario
        Forms\Components\Select::make('fabric_id')
            ->label('Da Inventario')
            ->relationship('fabric', 'name') // usa la relazione che creeremo in DressFabric
            ->searchable()
            ->preload()
            ->reactive()
            ->afterStateUpdated(function ($state, Set $set) {
                if ($state) {
                    $fabric = \App\Models\Fabric::find($state);
                    if ($fabric) {
                        $set('name', $fabric->name);
                        $set('type', $fabric->type);
                        $set('purchase_price', $fabric->purchase_price);
                        $set('client_price', $fabric->client_price);
                        $set('color_code', $fabric->color_code);
                        $set('supplier', $fabric->supplier);
                    }
                }
            }),

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
            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),

        Forms\Components\TextInput::make('purchase_price')
            ->label('Prezzo Acquisto')
            ->numeric()
            ->step(0.01)
            ->prefix('€')
            ->live(debounce: 300)
            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),

        Forms\Components\TextInput::make('client_price')
            ->label('Prezzo Cliente')
            ->numeric()
            ->step(0.01)
            ->prefix('€')
            ->live(debounce: 300)
            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),

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


                // Extra
                Forms\Components\Repeater::make('extras')
                    ->label('Extra Aggiuntivi')
                    ->relationship('extras')
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get))
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
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                    ->collapsible()
                    ->cloneable()
                    ->reorderableWithButtons()
                    ->addActionLabel('Aggiungi Extra'),
            ])
            ->columnSpanFull();
    }

    // --- SEZIONE 5: Misure (Tabs + ordine dal Model) ---
    private static function measurementsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Misure')
            ->schema([
                Forms\Components\Repeater::make('measurements')
                    ->label('Misure Cliente')
                    ->relationship('measurements')
                    ->schema([
                        Forms\Components\Tabs::make('Misure (ordine ufficiale)')
                            ->tabs(self::measurementTabs())
                            ->persistTabInQueryString()
                            ->contained(false)
                            ->lazy(),
                    ])
                    ->itemLabel('Misure')
                    ->maxItems(1)
                    ->defaultItems(1),
            ])
            ->columnSpanFull();
    }

    /**
     * Costruisce le Tabs rispettando l'ordine esatto di DressMeasurement::ORDERED_MEASURES.
     * Ogni tab richiama un sottoinsieme di campi (in ordine) per migliore UX.
     */
    private static function measurementTabs(): array
    {
        // Sorgente d'ordine (DB fields => label)
        $ordered = DressMeasurement::ORDERED_MEASURES;

        // Definizione dei gruppi (in termini di nomi colonna)
        // NOTA: l'ordine qui sotto è quello delle tabs; dentro ogni tab
        // i campi vengono presi nell'ordine esatto definito in ORDERED_MEASURES.
        $groups = [
            '1) Collo & Busto' => [
                'circonferenza_collo','torace','seno','sotto_seno','vita','bacino',
                'lunghezza_bacino','lunghezza_seno','distanza_seni','precisapince',
                'scollo','scollo_dietro','lunghezza_vita','lunghezza_vita_dietro',
            ],
            '2) Spalle & Torace' => [
                'larghezza_schiena','inclinazione_spalle','larghezza_torace_interno','lunghezza_taglio',
            ],
            '3) Capo & Gonna' => [
                'lunghezza_abito','lunghezza_gonna_avanti','lunghezza_gonna_dietro',
            ],
            '4) Maniche & Braccia' => [
                'lunghezza_gomito','lunghezza_manica','circonferenza_braccio','circonferenza_polso','livello_ascellare',
            ],
            '5) Pantalone' => [
                'lunghezza_pantalone_interno','lunghezza_pantalone_esterno','lunghezza_ginocchio','lunghezza_cavallo',
            ],
            '6) Circonferenze Gambe' => [
                'circonferenza_coscia','circonferenza_ginocchio','circonferenza_caviglia','circonferenza_taglio',
            ],
        ];

        $tabs = [];
        foreach ($groups as $title => $names) {
            $fields = self::pickMeasurementInputs($ordered, $names);
            $tabs[] = Forms\Components\Tabs\Tab::make($title)
                ->schema($fields)
                ->columns(4);
        }

        // Tab opzionale: campi legacy (se ancora vuoi mostrarli)
        $legacy = [
            'spalle' => 'Spalle (legacy)',
            'fianchi' => 'Fianchi (legacy)',
            'lunghezza_busto' => 'Lunghezza Busto (legacy)',
            'altezza_totale' => 'Altezza Totale (legacy)',
            'lunghezza_gonna' => 'Lunghezza Gonna (legacy)',
        ];
        $legacyFields = [];
        foreach ($legacy as $name => $label) {
            $legacyFields[] = self::buildMeasureInput($name, $label, 'cm');
        }
        if (!empty($legacyFields)) {
            $tabs[] = Forms\Components\Tabs\Tab::make('Legacy (opz.)')
                ->schema($legacyFields)
                ->columns(4);
        }

        return $tabs;
    }

    /**
     * Ritorna una lista di TextInput per i nomi richiesti ($names),
     * usando etichette prese da $ordered (ORDERED_MEASURES) e suffix coerente.
     */
    private static function pickMeasurementInputs(array $ordered, array $names): array
    {
        $inputs = [];

        // Manteniamo l'ordine di ORDERED_MEASURES:
        foreach ($ordered as $field => $label) {
            if (! in_array($field, $names, true)) {
                continue;
            }

            // Suffix: "°" solo per inclinazione_spalle, altrimenti "cm"
            $suffix = $field === 'inclinazione_spalle' ? '°' : 'cm';

            $inputs[] = self::buildMeasureInput($field, $label, $suffix);
        }

        return $inputs;
    }

    /** Crea il singolo TextInput misura con regole comuni */
    private static function buildMeasureInput(string $field, string $label, string $suffix): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($field)
            ->label($label)
            ->numeric()
            ->step(0.1)
            ->suffix($suffix);
    }


    // --- SEZIONE 6: Totali e Stato ---
    private static function totalsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Totali e Stato')
            ->schema([
                // Totali calcolati (sola lettura)
                Forms\Components\Grid::make(4)->schema([
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
                        
Forms\Components\TextInput::make('remaining')
    ->label('Rimanente da Pagare')
    ->prefix('€')
    ->disabled()
    ->dehydrated(true),   // <--- PRIMA era false
                ]),

                // Campi editabili
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('deposit')
                        ->label('Acconto')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('€')
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),

                Forms\Components\TextInput::make('manual_client_price')
                    ->label('Prezzo Manuale (deciso da Madamadorè)')
                    ->prefix('€')
                    ->numeric()
                    ->step(0.01)
                    ->placeholder('Es: 600')
                    ->helperText('Se impostato, sostituisce il Prezzo Cliente calcolato')
                    ->default(null),

                Forms\Components\Toggle::make('use_manual_price')
                    ->label('Usa Prezzo Manuale')
                    ->helperText('Se attivo, i calcoli useranno il Prezzo Manuale invece del calcolato')
                    ->default(false),


                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options(self::getStatusLabels())
                    ->default('in_attesa_acconto'),
                ]),
            ])
            ->columnSpanFull();
    }

    private static function bootCalcPlaceholder(): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make('_boot_calc')
            ->label('')
            ->content('')
            ->extraAttributes(['style' => 'display:none'])
            ->dehydrated(false)
            ->afterStateHydrated(function (Set $set, Get $get) {
                // 🚦 Early exit: evita loop e calcoli inutili
                static $done = false;
                if ($done) {
                    return;
                }
                $done = true;

                self::updateCalculations($set, $get);
            });
    }

}
