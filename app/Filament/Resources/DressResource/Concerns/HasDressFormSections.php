<?php

namespace App\Filament\Resources\DressResource\Concerns;

use App\Models\Fabric;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\DressCorset;
use App\Models\DressMeasurement;
use App\Services\DressFabricPhotoService;
use App\Services\MeasurementRecallService;
use App\Support\SingleFileUploadState;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;



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

                Forms\Components\Select::make('ceremony_holder')
                    ->label('Intestatario della Cerimonia')
                    ->options(fn () => \App\Models\Dress::query()
                        ->whereNotNull('ceremony_holder')
                        ->where('ceremony_holder', '!=', '')
                        ->orderBy('ceremony_holder')
                        ->pluck('ceremony_holder', 'ceremony_holder')
                        ->unique()
                        ->toArray()
                    )
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('ceremony_holder')
                            ->label('Nome intestatario')
                            ->required()
                            ->maxLength(255)
                    ])
                    ->createOptionUsing(function (array $data): string {
                        return $data['ceremony_holder'];
                    })
                    ->allowHtml(false)
                    ->preload()
                    ->helperText('Seleziona un intestatario esistente o creane uno nuovo'),
 // SOSTITUISCI tutto il blocco DatePicker::make('delivery_date') con questo:

Forms\Components\DatePicker::make('delivery_date')
    ->label('Data di Consegna Prevista')
    ->native(false)
    ->displayFormat('d/m/Y')
    ->closeOnDateSelection()
    ->live(debounce: 300)
    ->afterStateUpdated(function ($state, Set $set, Get $get, $livewire) {
        if ($state) {
            $currentDressId = $livewire->record?->id ?? null;

            $dressesOnDate = \App\Models\Dress::where('delivery_date', $state)
                ->when($currentDressId, fn($q) => $q->where('id', '!=', $currentDressId))
                ->get(['id', 'customer_name', 'ceremony_type']);

            $count = $dressesOnDate->count();

            if ($count === 0) {
                $helper = '🟢 Giornata libera - Perfetto per la consegna!';
            } elseif ($count <= 2) {
                $customers = $dressesOnDate->pluck('customer_name')->take(2)->join(', ');
                $helper = "🟡 {$count} abiti già previsti: {$customers}" . ($count > 2 ? ' e altri...' : '');
            } elseif ($count <= 4) {
                $customers = $dressesOnDate->pluck('customer_name')->take(2)->join(', ');
                $helper = "🟠 GIORNATA IMPEGNATIVA - {$count} abiti: {$customers}" . ($count > 2 ? ' e altri...' : '');
            } else {
                $customers = $dressesOnDate->pluck('customer_name')->take(2)->join(', ');
                $helper = "🔴 ATTENZIONE: GIORNATA SOVRACCARICA! {$count} abiti: {$customers} e altri...";
            }

            $set('delivery_date_helper', $helper);
        } else {
            $set('delivery_date_helper', '');
        }
    })
    ->extraAttributes([
    'x-data' => '{
        init() {
            this.$nextTick(() => {
                const panel = this.$el.closest(".fi-fo-field-wrp").querySelector(".fi-fo-date-time-picker-panel");
                if (panel) {
                    // Observer per quando il calendario diventa visibile
                    const observer = new MutationObserver(() => {
                        if (panel.style.display !== "none") {
                            this.colorCalendar();
                        }
                    });
                    observer.observe(panel, { attributes: true, attributeFilter: ["style"] });
                    
                    // Listener per cambio mese/anno
                    panel.addEventListener("change", () => {
                        setTimeout(() => this.colorCalendar(), 100);
                    });
                }
            });
        },
        async colorCalendar() {
            const panel = this.$el.closest(".fi-fo-field-wrp").querySelector(".fi-fo-date-time-picker-panel");
            if (!panel || panel.style.display === "none") return;
            
            // Leggi mese e anno dai select/input di Filament
            const monthSelect = panel.querySelector("select");
            const yearInput = panel.querySelector("input[type=number]");
            
            if (!monthSelect || !yearInput) return;
            
            const month = parseInt(monthSelect.value) + 1; // 0-based to 1-based
            const year = parseInt(yearInput.value);
            
            try {
                const response = await fetch("/admin/calendar/availability", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content")
                    },
                    body: JSON.stringify({
                        model: "App\\\\Models\\\\Dress",
                        date_column: "delivery_date",
                        month: month,
                        year: year
                    })
                });
                
                const data = await response.json();
                this.applyColors(panel, data.data || {}, month, year);
            } catch (error) {
                console.error("Errore caricamento calendario:", error);
            }
        },
        applyColors(panel, availabilityData, month, year) {
            const dayDivs = panel.querySelectorAll("div[role=option]");
            
            dayDivs.forEach(dayDiv => {
                const dayNumber = parseInt(dayDiv.textContent);
                if (isNaN(dayNumber)) return;
                
                const dateKey = year + "-" + String(month).padStart(2, "0") + "-" + String(dayNumber).padStart(2, "0");
                const availability = availabilityData[dateKey];
                const count = availability ? availability.count : 0;
                
                if (count === 0) {
                    dayDiv.style.cssText = "background-color: #22c55e !important; color: white !important; border-radius: 50%;";
                } else if (count === 1) {
                    dayDiv.style.cssText = "background-color: #f59e0b !important; color: white !important; border-radius: 50%;";
                } else {
                    dayDiv.style.cssText = "background-color: #ef4444 !important; color: white !important; border-radius: 50%;";
                }
            });
        }
    }'
])
    ->helperText('🟢 Verde = libera, 🟡 Arancione = 1 abito, 🔴 Rosso = 2+ abiti'),

// Aggiungi il Placeholder helper dinamico subito dopo
Forms\Components\Placeholder::make('delivery_date_helper')
    ->label('')
    ->content(fn ($get) => $get('delivery_date_helper') ?: '')
    ->visible(fn ($get) => !empty($get('delivery_date_helper')))
    ->extraAttributes([
        'class' => 'delivery-date-info',
        'style' => 'background-color:#21242b !important;'
    ])
    ->dehydrated(false),
        ])
        ->columns(2);
    }
private static function imagesSection(): Forms\Components\Section
{
    return Forms\Components\Section::make('Immagini Abito')
        ->schema([
            // Row 1: Bozza e Definitivo (2 colonne)
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\FileUpload::make('sketch_image')
                        ->label('Bozza')
                        ->image()
                        ->disk('public')
                        ->directory('dress-sketches')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->downloadable(),

                    Forms\Components\FileUpload::make('final_image')
                        ->label('Definitivo')
                        ->image()
                        ->disk('public')
                        ->directory('dress-finals')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/*'])
                        ->downloadable(),
                ]),

            // Row 2: Bottoni di visualizzazione
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('view_sketch')
                    ->label('🔍 Visualizza Bozza')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Visualizza Bozza')
                    ->modalContent(fn($record) => $record?->sketch_image
                        ? view('filament.modals.image-viewer', ['imagePath' => $record->sketch_image, 'title' => 'Bozza'])
                        : view('filament.modals.image-viewer', ['imagePath' => null, 'title' => 'Bozza'])
                    )
                    ->modal()
                    ->visible(fn($record) => $record?->sketch_image),

                Forms\Components\Actions\Action::make('view_final')
                    ->label('🔍 Visualizza Definitivo')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->modalHeading('Visualizza Definitivo')
                    ->modalContent(fn($record) => $record?->final_image
                        ? view('filament.modals.image-viewer', ['imagePath' => $record->final_image, 'title' => 'Definitivo'])
                        : view('filament.modals.image-viewer', ['imagePath' => null, 'title' => 'Definitivo'])
                    )
                    ->modal()
                    ->visible(fn($record) => $record?->final_image),

                Forms\Components\Actions\Action::make('view_drawing')
                    ->label('🔍 Visualizza Disegno')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->modalHeading('Visualizza Disegno')
                    ->modalContent(fn($record) => $record?->drawing_image
                        ? view('filament.modals.image-viewer', ['imagePath' => $record->drawing_image, 'title' => 'Disegno Salvato'])
                        : view('filament.modals.image-viewer', ['imagePath' => null, 'title' => 'Disegno Salvato'])
                    )
                    ->modal()
                    ->visible(fn($record) => $record?->drawing_image),
            ])
            ->columnSpanFull(),

            // Row 2: SignaturePad GRANDE (full width - 12 colonne)
            SignaturePad::make('drawing_pad')
                ->label('Disegna abito (schizzo monocolore)')
                ->penColor('#ffffff')
                ->exportPenColor('#000000')
                ->backgroundColor('#805D93')
                ->exportBackgroundColor('#ffffff')
                ->lineMinWidth(0.9)
                ->lineMaxWidth(2.8)
                ->undoable()
                ->clearable()
                ->confirmable()
                ->helperText('Disegna e premi "Done" per fissare lo schizzo. Verrà salvato come immagine.')
                ->columnSpan('full'),

            // Row 3: Preview salvato (full width)
            Forms\Components\FileUpload::make('drawing_image')
                ->label('Disegno salvato')
                ->image()
                ->disk('public')
                ->directory('dress-drawings')
                ->visibility('public')
                ->downloadable()
                ->dehydrated(false)
                ->disabled()
                ->columnSpan('full'),

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

    private static function expenseSection(): Forms\Components\Section
{
    return Forms\Components\Section::make('Lista della spesa')
        ->description('Prodotti o materiali utilizzati per la realizzazione dell’abito (uso interno)')
        ->schema([
            Forms\Components\Repeater::make('expenses')
                ->label('Articoli')
                ->relationship('expenses')
                ->schema([
                    Forms\Components\TextInput::make('name')
    ->label('Nome articolo')
    ->default('Articolo generico')
    ->placeholder('Es. Bottone, zip, decorazione...'),
                    
                    Forms\Components\FileUpload::make('photo_path')
                        ->label('Foto')
                        ->image()
                        ->directory('dress-expenses')
                        ->imageEditor(),
                    
                    Forms\Components\TextInput::make('price')
                        ->label('Costo interno (€)')
                        ->numeric()
                        ->prefix('€')
                        ->default(0)
                        ->helperText('Non influisce sul prezzo cliente.'),
                ])
                ->columns(3)
                ->addActionLabel('Aggiungi articolo')
                ->collapsible()
                ->cloneable(),
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

                Forms\Components\Repeater::make('fabrics')
                    ->label('Tessuti')
                    ->relationship('fabrics')
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get))
                    ->schema([
                        Forms\Components\Select::make('fabric_id')
                            ->label('Da Inventario')
                            ->relationship('fabric', 'name')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::fillFabricFieldsFromInventory(
                                $state ? (int) $state : null,
                                $set,
                                $get,
                            )),

                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto tessuto per preventivo')
                            ->image()
                            ->disk('public')
                            ->directory('dress-fabrics')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/*'])
                            ->imageEditor()
                            ->downloadable()
                            ->helperText('Se scegli un tessuto da inventario, la foto viene precompilata. Puoi comunque sostituirla manualmente.')
                            ->columnSpanFull(),

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
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get))
                            ->hidden()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('client_price')
                            ->label('Prezzo Cliente')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->live(debounce: 300)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get))
                            ->hidden()
                            ->dehydrated(),

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

    private static function fillFabricFieldsFromInventory(?int $fabricId, Set $set, Get $get): void
    {
        if (! $fabricId) {
            self::updateCalculations($set, $get);

            return;
        }

        $fabric = Fabric::query()->find($fabricId);

        if (! $fabric) {
            self::updateCalculations($set, $get);

            return;
        }

        $set('name', $fabric->name);
        $set('type', $fabric->type);
        $set('purchase_price', $fabric->purchase_price);
        $set('client_price', $fabric->client_price);
        $set('color_code', $fabric->color_code);
        $set('supplier', $fabric->supplier);
        $set('photo_path', SingleFileUploadState::fromPath(
            app(DressFabricPhotoService::class)->copyFromInventory($fabric)
        ));

        self::updateCalculations($set, $get);
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

                // --- Repeater per Misure Personalizzate ---
                Forms\Components\Repeater::make('customMeasurements')
                    ->label('Misure Personalizzate')
                    ->relationship('customMeasurements')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('Nome Misura')
                            ->required()
                            ->placeholder('es: Circonferenza polpaccio sinistro')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('value')
                            ->label('Valore')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('cm')
                            ->placeholder('Valore opzionale'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Note')
                            ->rows(2)
                            ->placeholder('Note aggiuntive...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Misura personalizzata')
                    ->collapsible()
                    ->cloneable()
                    ->reorderableWithButtons()
                    ->addActionLabel('Aggiungi Misura Personalizzata')
                    ->defaultItems(0),

                    Actions::make([
    Action::make('recall_measurements')
        ->label('Ricorda misure')
        ->icon('heroicon-o-clipboard-document-list')
        ->modalHeading('Ricorda misure da cliente')
        ->modalSubmitActionLabel('Importa misure')
        ->form([
            Forms\Components\Select::make('customer_key')
                ->label('Cliente')
                ->options(fn () => MeasurementRecallService::distinctCustomersWithLastEvent())
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Seleziona il cliente da cui importare le ultime misure.'),

            Forms\Components\Radio::make('mode')
                ->label('Modalità di import')
                ->options([
                    'replace' => 'Sostituisci tutto',
                    'fill'    => 'Completa solo i campi vuoti',
                ])
                ->default('replace')
                ->inline(),

            Forms\Components\Toggle::make('include_custom')
                ->label('Includi misure personalizzate')
                ->default(true),

            Forms\Components\Toggle::make('merge_custom_by_label')
                ->label('Evita duplicati per etichetta (solo in "Completa")')
                ->default(true)
                ->visible(fn (Get $get) => $get('mode') === 'fill'),

            Forms\Components\Toggle::make('include_corsets')
                ->label('Includi misure corsetto')
                ->default(true),
        ])
        ->action(function (array $data, Set $set, Get $get, $livewire) {

        $currentMeasurements = $get('measurements') ?? [];
        $currentCustoms      = $get('customMeasurements') ?? [];
        $currentCorsets      = $get('corsets') ?? [];
        $excludeId           = $livewire->record->id ?? null;

        $result = MeasurementRecallService::recallForCustomerKey(
            $data['customer_key'],
            $excludeId,
            $currentMeasurements,
            $currentCustoms,
            $data['mode'] ?? 'replace',
            (bool) ($data['include_custom'] ?? true),
            (bool) ($data['merge_custom_by_label'] ?? true),
            (bool) ($data['include_corsets'] ?? true),
            $currentCorsets,
        );

        // ✔ Importa misure
        $set('measurements', $result['measurements']);
        $set('customMeasurements', $result['customMeasurements']);
        $set('corsets', $result['corsets']);

        // ✔ Importa anche NOME e TELEFONO
        if ($result['sourceCustomerName']) {
            $set('customer_name', $result['sourceCustomerName']);
            $set('phone_number', $result['sourcePhoneNumber']);
        }

        // Notifiche
        if (!empty($result['sourceDressId'])) {
            Notification::make()
                ->title('Misure importate')
                ->body('Prese da Abito #'.$result['sourceDressId'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Nessuna misura trovata per il cliente selezionato')
                ->warning()
                ->send();
        }
    }),
]),

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
                Forms\Components\Section::make('Valori economici')
                    ->description('Apri questa sezione per vedere e gestire i valori economici dell\'abito.')
                    ->schema([
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
                                ->dehydrated(true),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
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
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

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

                    Forms\Components\Select::make('status')
                        ->label('Stato')
                        ->options(self::getStatusLabels())
                        ->default('in_attesa_acconto'),
                ]),
            ])
            ->columnSpanFull();
    }

    private static function corsetsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Misure Corsetto')
            ->schema([
                Forms\Components\Repeater::make('corsets')
                    ->label('Corsetti')
                    ->relationship('corsets')
                    ->schema([
                        Forms\Components\Fieldset::make('Misura')
                            ->schema([
                                self::corsetDimensionalMeasurementsHeader(),
                                ...self::corsetDimensionalMeasurementRows(),
                                Forms\Components\Placeholder::make('corset_structural_measurements_title')
                                    ->label('')
                                    ->content(new HtmlString('<span class="font-semibold">Misure strutturali</span>')),
                                ...self::corsetStructuralMeasurementRows(),
                                self::corsetSupportFieldset(),
                            ]),

                        Forms\Components\Fieldset::make('Riprese')
                            ->schema([
                                self::corsetRipreseFieldset('vita'),
                                self::corsetRipreseFieldset('fianchi'),
                            ])
                            ->columns(2),
                    ])
                    ->itemLabel('Corsetto')
                    ->collapsible()
                    ->cloneable()
                    ->reorderableWithButtons()
                    ->addActionLabel('Aggiungi Corsetto')
                    ->defaultItems(0),
            ])
            ->columnSpanFull();
    }

    private static function corsetDimensionalMeasurementsHeader(): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(4)
            ->schema([
                self::corsetHeaderPlaceholder('corset_dimensional_header_measure', 'Misura'),
                self::corsetHeaderPlaceholder('corset_dimensional_header_cm', 'cm'),
                self::corsetHeaderPlaceholder('corset_dimensional_header_half', '1/2'),
                self::corsetHeaderPlaceholder('corset_dimensional_header_quarter', '1/4'),
            ]);
    }

    private static function corsetDimensionalMeasurementRows(): array
    {
        $rows = [];

        foreach (DressCorset::DIMENSIONAL_MEASUREMENTS as $field => $label) {
            $rows[] = Forms\Components\Grid::make(4)
                ->schema([
                    self::corsetLabelPlaceholder("{$field}_label", $label),
                    self::corsetMeasurementInput($field),
                    self::corsetDerivedValuePlaceholder("{$field}_half", $field, 2),
                    self::corsetDerivedValuePlaceholder("{$field}_quarter", $field, 4),
                ]);
        }

        return $rows;
    }

    private static function corsetStructuralMeasurementRows(): array
    {
        $rows = [];

        foreach (DressCorset::STRUCTURAL_MEASUREMENTS as $field => $label) {
            $rows[] = Forms\Components\Grid::make(2)
                ->schema([
                    self::corsetLabelPlaceholder("{$field}_label", $label),
                    self::corsetMeasurementInput($field, $field === 'linea_sotto_seno'),
                ]);
        }

        return $rows;
    }

    private static function corsetSupportFieldset(): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make('Supporto calcolo linea sotto il seno')
            ->schema([
                Forms\Components\Placeholder::make('corset_larghezza_seno_formula')
                    ->label('Formula larghezza seno')
                    ->content(fn (Get $get) => self::buildLarghezzaSenoFormulaSummary(
                        self::nullableFloat($get('circonferenza_seno'))
                    )),

                Forms\Components\Placeholder::make('corset_larghezza_seno_value')
                    ->label('Larghezza seno suggerita')
                    ->content(fn (Get $get) => self::formatCorsetValue(
                        DressCorset::calculateLarghezzaSeno(self::nullableFloat($get('circonferenza_seno')))
                    )),

                Forms\Components\Placeholder::make('corset_linea_sotto_seno_formula')
                    ->label('Formula linea sotto il seno')
                    ->content('1/2 Larghezza seno - 1/40 Circonferenza seno'),

                Forms\Components\Placeholder::make('corset_linea_sotto_seno_value')
                    ->label('Linea sotto il seno suggerita')
                    ->content(fn (Get $get) => self::formatCorsetValue(
                        DressCorset::calculateLineaSottoSenoSuggerita(self::nullableFloat($get('circonferenza_seno')))
                    )),
            ])
            ->columns(2);
    }

    private static function corsetRipreseFieldset(string $group): Forms\Components\Fieldset
    {
        $groupDefinition = DressCorset::RIPRESA_GROUPS[$group];
        $fields = [];

        foreach ($groupDefinition['fields'] as $field => $label) {
            $fields[] = Forms\Components\TextInput::make($field)
                ->label($label)
                ->numeric()
                ->step(0.1)
                ->suffix('cm');
        }

        array_unshift(
            $fields,
            Forms\Components\Placeholder::make("riprese_{$group}_formula")
                ->label('')
                ->content(new HtmlString('<span class="text-sm text-gray-600">' . e($groupDefinition['formula']) . '</span>'))
                ->columnSpanFull(),
        );

        return Forms\Components\Fieldset::make($groupDefinition['label'])
            ->schema($fields)
            ->columns(3);
    }

    private static function corsetHeaderPlaceholder(string $name, string $label): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make($name)
            ->label('')
            ->content(new HtmlString('<span class="font-semibold">' . e($label) . '</span>'));
    }

    private static function corsetLabelPlaceholder(string $name, string $label): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make($name)
            ->label('')
            ->content($label);
    }

    private static function corsetMeasurementInput(string $field, bool $withCalculatedHelper = false): Forms\Components\TextInput
    {
        $input = Forms\Components\TextInput::make($field)
            ->label('')
            ->numeric()
            ->step(0.1)
            ->suffix('cm');

        if (! $withCalculatedHelper) {
            return $input;
        }

        return $input->helperText(fn (Get $get) => self::buildLineaSottoSenoHelperText(
            self::nullableFloat($get('circonferenza_seno'))
        ));
    }

    private static function corsetDerivedValuePlaceholder(
        string $name,
        string $field,
        int $divisor,
    ): Forms\Components\Placeholder {
        return Forms\Components\Placeholder::make($name)
            ->label('')
            ->content(fn (Get $get) => self::formatCorsetValue(
                self::calculateDerivedCorsetValue(self::nullableFloat($get($field)), $divisor)
            ));
    }

    private static function calculateDerivedCorsetValue(?float $value, int $divisor): ?float
    {
        if ($value === null || $divisor <= 0) {
            return null;
        }

        return round($value / $divisor, 1);
    }

    private static function buildLarghezzaSenoFormulaSummary(?float $circSeno): string
    {
        $formula = DressCorset::larghezzaSenoFormulaFor($circSeno);

        if ($formula === null) {
            return 'Disponibile per circonferenza seno tra 80 e 116 cm';
        }

        return $formula;
    }

    private static function buildLineaSottoSenoHelperText(?float $circSeno): string
    {
        $suggestedValue = DressCorset::calculateLineaSottoSenoSuggerita($circSeno);

        if ($suggestedValue === null) {
            return 'Inserisci la circonferenza seno per ottenere un valore suggerito.';
        }

        return 'Valore suggerito: ' . self::formatCorsetValue($suggestedValue);
    }

    private static function formatCorsetValue(?float $value): string
    {
        if ($value === null) {
            return '—';
        }

        return number_format($value, 1, ',', '.') . ' cm';
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
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
