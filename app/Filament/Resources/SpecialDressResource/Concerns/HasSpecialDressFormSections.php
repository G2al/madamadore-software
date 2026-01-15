<?php

namespace App\Filament\Resources\SpecialDressResource\Concerns;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\SpecialDressMeasurement;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;

trait HasSpecialDressFormSections
{
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

                Forms\Components\Select::make('ceremony_type')
                    ->label('Festività')
                    ->options(fn () => \App\Models\Ceremony::orderBy('sort_order')->pluck('name', 'name'))
                    ->searchable()
                    ->required()
                    ->createOptionAction(function (Forms\Components\Actions\CreateAction $action) {
                        return $action
                            ->modalHeading('Crea nuova festività')
                            ->modalSubmitActionLabel('Crea')
                            ->modalCancelActionLabel('Annulla')
                            ->form([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome Festività')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('icon')
                                    ->label('Emoji/Icona')
                                    ->hint('Es: 💒, 🎂, 🎈, etc')
                                    ->maxLength(10),
                            ])
                            ->action(function (array $data) {
                                \App\Models\Ceremony::create([
                                    'name' => $data['name'],
                                    'icon' => $data['icon'] ?? '✨',
                                    'sort_order' => 999,
                                ]);
                            });
                    }),

                Forms\Components\TextInput::make('character')
                    ->label('Personaggio/Maschera')
                    ->placeholder('es: Cenerentola, Batman, Superman...')
                    ->maxLength(255),

                Forms\Components\DatePicker::make('delivery_date')
                    ->label('Data di Consegna Prevista')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, Set $set, Get $get, $livewire) {
                        if ($state) {
                            $currentId = $livewire->record?->id ?? null;

                            $onDate = \App\Models\SpecialDress::where('delivery_date', $state)
                                ->when($currentId, fn($q) => $q->where('id', '!=', $currentId))
                                ->get(['id','customer_name','ceremony_type']);

                            $count = $onDate->count();
                            if ($count === 0) {
                                $helper = '🟢 Giornata libera - Perfetto per la consegna!';
                            } elseif ($count <= 2) {
                                $customers = $onDate->pluck('customer_name')->take(2)->join(', ');
                                $helper = "🟡 {$count} abiti già previsti: {$customers}";
                            } elseif ($count <= 4) {
                                $customers = $onDate->pluck('customer_name')->take(2)->join(', ');
                                $helper = "🟠 GIORNATA IMPEGNATIVA - {$count} abiti: {$customers}";
                            } else {
                                $customers = $onDate->pluck('customer_name')->take(2)->join(', ');
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
                                        const observer = new MutationObserver(() => {
                                            if (panel.style.display !== "none") {
                                                this.colorCalendar();
                                            }
                                        });
                                        observer.observe(panel, { attributes: true, attributeFilter: ["style"] });
                                        panel.addEventListener("change", () => {
                                            setTimeout(() => this.colorCalendar(), 100);
                                        });
                                    }
                                });
                            },
                            async colorCalendar() {
                                const panel = this.$el.closest(".fi-fo-field-wrp").querySelector(".fi-fo-date-time-picker-panel");
                                if (!panel || panel.style.display === "none") return;

                                const monthSelect = panel.querySelector("select");
                                const yearInput = panel.querySelector("input[type=number]");
                                if (!monthSelect || !yearInput) return;

                                const month = parseInt(monthSelect.value) + 1;
                                const year = parseInt(yearInput.value);

                                try {
                                    const response = await fetch("/admin/calendar/availability", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                            "X-CSRF-TOKEN": document.querySelector(\'meta[name=\"csrf-token\"]\').getAttribute("content")
                                        },
                                        body: JSON.stringify({
                                            model: "App\\\\Models\\\\SpecialDress",
                                            date_column: "delivery_date",
                                            month: month,
                                            year: year
                                        })
                                    });
                                    const data = await response.json();
                                    this.applyColors(panel, data.data || {}, month, year);
                                } catch (e) { console.error(e); }
                            },
                            applyColors(panel, availabilityData, month, year) {
                                const dayDivs = panel.querySelectorAll("div[role=option]");
                                dayDivs.forEach(dayDiv => {
                                    const dayNumber = parseInt(dayDiv.textContent);
                                    if (isNaN(dayNumber)) return;
                                    const dateKey = year + "-" + String(month).padStart(2,"0") + "-" + String(dayNumber).padStart(2,"0");
                                    const availability = availabilityData[dateKey];
                                    const count = availability ? availability.count : 0;

                                    if (count === 0) {
                                        dayDiv.style.cssText = "background-color:#22c55e!important;color:white!important;border-radius:50%;";
                                    } else if (count === 1) {
                                        dayDiv.style.cssText = "background-color:#f59e0b!important;color:white!important;border-radius:50%;";
                                    } else {
                                        dayDiv.style.cssText = "background-color:#ef4444!important;color:white!important;border-radius:50%;";
                                    }
                                });
                            }
                        }'
                    ])
                    ->helperText('🟢 Verde = libera, 🟡 Arancione = 1 abito, 🔴 Rosso = 2+ abiti'),

                Forms\Components\Placeholder::make('delivery_date_helper')
                    ->label('')
                    ->content(fn ($get) => $get('delivery_date_helper') ?: '')
                    ->visible(fn ($get) => !empty($get('delivery_date_helper')))
                    ->extraAttributes(['class' => 'delivery-date-info', 'style' => 'background-color:#21242b !important;'])
                    ->dehydrated(false),
            ])
            ->columns(2);
    }

    private static function imagesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Immagini')
            ->schema([
                Forms\Components\FileUpload::make('sketch_image')
                    ->label('Bozza')->image()->disk('public')->directory('special-dress-sketches')
                    ->visibility('public')->acceptedFileTypes(['image/*'])->downloadable(),

                Forms\Components\FileUpload::make('final_image')
                    ->label('Definitivo')->image()->disk('public')->directory('special-dress-finals')
                    ->visibility('public')->acceptedFileTypes(['image/*'])->downloadable(),
            ])
            ->columns(2);
    }

    private static function notesSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Note')
            ->schema([
                Forms\Components\Textarea::make('notes')->label('Annotazioni')->rows(4),
            ])->columnSpanFull();
    }

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
                ->options(fn () => \App\Services\MeasurementRecallService::distinctCustomersWithLastEvent())
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
        ])
        ->action(function (array $data, Set $set, Get $get, $livewire) {

            $currentMeasurements = $get('measurements') ?? [];
            $currentCustoms      = $get('customMeasurements') ?? [];
            $excludeId           = $livewire->record->id ?? null;

            $result = \App\Services\MeasurementRecallService::recallForCustomerKey(
                $data['customer_key'],
                $excludeId,
                $currentMeasurements,
                $currentCustoms,
                $data['mode'] ?? 'replace',
                (bool) ($data['include_custom'] ?? true),
                (bool) ($data['merge_custom_by_label'] ?? true),
            );

            // ✔ importa misure
            $set('measurements', $result['measurements']);

            // ✔ importa misure custom SOLO SE ESISTONO nel modello SpecialDress
            if ($get('customMeasurements') !== null) {
                $set('customMeasurements', $result['customMeasurements']);
            }

            // ✔ importa NOME e TELEFONO
            if ($result['sourceCustomerName']) {
                $set('customer_name', $result['sourceCustomerName']);
                $set('phone_number', $result['sourcePhoneNumber']);
            }

            // ✔ Notifiche
            if (!empty($result['sourceDressId'])) {
                Notification::make()
                    ->title('Misure importate')
                    ->body('Prese da Abito #' . $result['sourceDressId'])
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

            ])->columnSpanFull();
    }

    private static function measurementTabs(): array
    {
        $ordered = SpecialDressMeasurement::ORDERED_MEASURES;
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
            $tabs[] = Forms\Components\Tabs\Tab::make($title)->schema($fields)->columns(4);
        }

        // legacy opzionale
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
            $tabs[] = Forms\Components\Tabs\Tab::make('Legacy (opz.)')->schema($legacyFields)->columns(4);
        }

        return $tabs;
    }

    private static function pickMeasurementInputs(array $ordered, array $names): array
    {
        $inputs = [];
        foreach ($ordered as $field => $label) {
            if (! in_array($field, $names, true)) continue;
            $suffix = $field === 'inclinazione_spalle' ? '°' : 'cm';
            $inputs[] = self::buildMeasureInput($field, $label, $suffix);
        }
        return $inputs;
    }

    private static function buildMeasureInput(string $field, string $label, string $suffix): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($field)
            ->label($label)
            ->numeric()
            ->step(0.1)
            ->suffix($suffix);
    }

    private static function totalsSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Prezzi')
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('total_client_price')
                        ->label('Prezzo Totale')
                        ->prefix('€')
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),

                    Forms\Components\TextInput::make('deposit')
                        ->label('Acconto')
                        ->prefix('€')
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->live(debounce: 300)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::updateCalculations($set, $get)),

                    Forms\Components\TextInput::make('remaining')
                        ->label('Rimanente')
                        ->prefix('€')
                        ->disabled()
                        ->dehydrated(true),
                ]),

                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options(self::getStatusLabels())
                    ->default('in_attesa_acconto'),
            ])->columnSpanFull();
    }

    private static function bootCalcPlaceholder(): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make('_boot_calc')
            ->label('')
            ->content('')
            ->extraAttributes(['style' => 'display:none'])
            ->dehydrated(false)
            ->afterStateHydrated(function (Set $set, Get $get) {
                static $done = false;
                if ($done) return;
                $done = true;
                // calcolo iniziale
                $price   = (float) ($get('total_client_price') ?? 0);
                $deposit = (float) ($get('deposit') ?? 0);
                $set('remaining', number_format(max($price - $deposit, 0), 2, '.', ''));
            });
    }
}
