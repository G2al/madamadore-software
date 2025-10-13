<?php

namespace App\Filament\Resources\AdjustmentResource\Concerns;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Resources\AdjustmentResource;
use App\Models\Customer;

// alias utili per i bottoni nel form
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;

trait HasAdjustmentFormSections
{
    /**
     * Sezione: Dati cliente
     */
  protected static function clientSection(): Forms\Components\Section
{
    return Forms\Components\Section::make('Dati cliente')
        ->schema([
            Forms\Components\Select::make('customer_id')
                ->label('Cliente')
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->reactive()
                ->afterStateUpdated(function ($state, Set $set) {
                    // Quando cambio cliente, aggiorno il telefono visualizzato
                    $set('customer_phone', \App\Models\Customer::find($state)?->phone_number);
                })
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome cliente')
                        ->default(''),
                    Forms\Components\TextInput::make('phone_number')
                        ->label('Numero di Cellulare')
                        ->tel()
                        ->maxLength(255)
                        ->unique(table: Customer::class, column: 'phone_number', ignoreRecord: true)
                        ->validationMessages([
                            'unique' => 'Questo numero di telefono è già stato registrato per un altro cliente.',
                        ])
                ]),

                Forms\Components\TextInput::make('referente')
    ->label('Referente')
    ->placeholder('Es. Marco, Giulia, Negozio Centro...')
    ->maxLength(255),

            // Campo status con logica corretta
            Forms\Components\Select::make('status')
                ->label('Stato Lavoro')
                ->options(\App\Models\Adjustment::getStatusLabels())
                ->default('in_lavorazione')
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                    // Logica: quando passa da 'in_lavorazione' a 'confermato' (bottone completato)
                    // Questa logica viene gestita dal bottone, non qui
                }),

            // Spunta ritirato (logica spostata nella table)
            Forms\Components\Toggle::make('ritirato')
                ->label('Ritirato dal Cliente')
                ->default(false)
                ->helperText('Attivare quando il cliente ha ritirato l\'aggiusto')
                ->onColor('success')
                ->offColor('info'),

            // Telefono di sola lettura con icona WhatsApp nel suffix
            Forms\Components\TextInput::make('customer_phone')
                ->label('Telefono cliente')
                ->readOnly()
                ->dehydrated(false)
                ->afterStateHydrated(function (
                    \Filament\Forms\Components\TextInput $component,
                    $state,
                    $record
                ) {
                    $component->state($record?->customer?->phone_number);
                })
                ->suffixIcon('heroicon-o-chat-bubble-left-right')
                ->suffixIconColor('success')
                ->extraAttributes(function (Get $get) {
                    $phone = \App\Models\Customer::find($get('customer_id'))?->phone_number;
                    if (! $phone) {
                        return ['class' => 'cursor-not-allowed opacity-60'];
                    }
                    $digits = preg_replace('/\D+/', '', $phone);
                    return [
                        'class'   => 'cursor-pointer',
                        'onclick' => "window.open('https://wa.me/{$digits}', '_blank');",
                    ];
                }),
Forms\Components\DatePicker::make('delivery_date')
    ->label('Data di consegna')
    ->native(false)
    ->displayFormat('d/m/Y')
    ->closeOnDateSelection()
    ->live(debounce: 300)
    ->afterStateUpdated(function ($state, Set $set, Get $get, $livewire) {
        if ($state) {
            $currentAdjustmentId = $livewire->record?->id ?? null;

            // Conta TUTTI gli aggiusti (normali + aziendali) per quella data
            $normalAdjustments = \App\Models\Adjustment::where('delivery_date', $state)
                ->when($currentAdjustmentId, fn($q) => $q->where('id', '!=', $currentAdjustmentId))
                ->count();
            $companyAdjustments = \App\Models\CompanyAdjustment::where('delivery_date', $state)->count();

            $count = $normalAdjustments + $companyAdjustments;

            // Recupera nomi clienti (sia da adjustments normali che aziendali)
            $normalCustomers = \App\Models\Adjustment::where('delivery_date', $state)
                ->when($currentAdjustmentId, fn($q) => $q->where('id', '!=', $currentAdjustmentId))
                ->with('customer')
                ->get()
                ->pluck('customer.name')
                ->filter();

            $companyCustomers = \App\Models\CompanyAdjustment::where('delivery_date', $state)
                ->with('customer')
                ->get()
                ->pluck('customer.name')
                ->filter();

            $allCustomers = $normalCustomers->concat($companyCustomers);

            if ($count === 0 || $count <= 2) {
                $customers = $allCustomers->take(2)->join(', ');
                $helper = $count === 0 
                    ? '🟢 Giornata libera - Perfetto per la consegna!' 
                    : "🟢 {$count} aggiusti già previsti: {$customers}";
            } elseif ($count <= 4) {
                $customers = $allCustomers->take(2)->join(', ');
                $helper = "🟡 GIORNATA IMPEGNATIVA - {$count} aggiusti: {$customers}" . ($count > 2 ? ' e altri...' : '');
            } else {
                $customers = $allCustomers->take(2)->join(', ');
                $helper = "🔴 ATTENZIONE: GIORNATA SOVRACCARICA! {$count} aggiusti: {$customers} e altri...";
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
                            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content")
                        },
                        body: JSON.stringify({
                            models: ["App\\\\Models\\\\Adjustment", "App\\\\Models\\\\CompanyAdjustment"],
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
                    
                    if (count <= 2) {
                        // Verde: 0-2 aggiusti
                        dayDiv.style.cssText = "background-color: #22c55e !important; color: white !important; border-radius: 50%;";
                    } else if (count <= 4) {
                        // Giallo: 3-4 aggiusti
                        dayDiv.style.cssText = "background-color: #f59e0b !important; color: white !important; border-radius: 50%;";
                    } else {
                        // Rosso: 5+ aggiusti
                        dayDiv.style.cssText = "background-color: #ef4444 !important; color: white !important; border-radius: 50%;";
                    }
                });
            }
        }'
    ])
    ->helperText('🟢 Verde = libera, 🟡 Arancione = 1-2 aggiusti, 🔴 Rosso = 3+ aggiusti'),

// Placeholder helper dinamico
Forms\Components\Placeholder::make('delivery_date_helper')
    ->label('')
    ->content(fn ($get) => $get('delivery_date_helper') ?: '')
    ->visible(fn ($get) => !empty($get('delivery_date_helper')))
    ->extraAttributes([
        'class' => 'delivery-date-info',
        'style' => 'background-color:#21242b !important;'
    ])
    ->dehydrated(false),

            // Repeater per gli aggiusti
            Forms\Components\Repeater::make('items')
                ->label('Aggiusti')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome aggiusto')
                        ->placeholder('es. Pantalone nero, Camicia bianca...')
                        ->required()
                        ->validationMessages([
                            'required' => 'Il nome dell\'aggiusto è obbligatorio. Inserisci cosa deve essere aggiustato.',
                        ])
                        ->columnSpan(1),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Descrizione lavoro')
                        ->placeholder('Descrivi cosa è stato fatto...')
                        ->rows(3)
                        ->columnSpan(2),
                ])
                ->columns(3)
                ->addActionLabel('Aggiungi aggiusto')
                ->defaultItems(1)
                ->collapsible()
                ->cloneable(),
        ]);
}
    /**
     * Sezione: Pagamento
     */
    protected static function paymentSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pagamento')
            ->schema([
                Forms\Components\TextInput::make('client_price')
                    ->label('Prezzo cliente')
                    ->numeric()
                    ->default(0)
                    ->rules(['gte:0'])
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => AdjustmentResource::updateCalculations($set, $get)),

                Forms\Components\TextInput::make('deposit')
                    ->label('Acconto')
                    ->numeric()
                    ->default(0)
                    ->rules(['nullable', 'gte:0'])
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => AdjustmentResource::updateCalculations($set, $get)),

                Forms\Components\TextInput::make('total')
                    ->label('Totale')
                    ->numeric()
                    ->default(0)
                    ->readOnly(),

                Forms\Components\TextInput::make('remaining')
                    ->label('Rimanente')
                    ->numeric()
                    ->default(0)
                    ->readOnly(),

                Forms\Components\TextInput::make('profit')
                    ->label('Guadagno')
                    ->numeric()
                    ->default(0)
                    ->readOnly(),
            ]);
    }

        /**
     * Sezione: Lista della spesa
     */
    protected static function expenseSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Lista della spesa')
            ->description('Prodotti o materiali utilizzati per l’aggiusto (uso interno, non visibili al cliente)')
            ->schema([
                Forms\Components\Repeater::make('expenses')
                    ->label('Articoli')
                    ->relationship() // usa la relazione hasMany(AdjustmentExpense)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome articolo')
                            ->default('Articolo generico')
                            ->placeholder('Es. Bottone, filo, cerniera...'),

                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->directory('expenses')
                            ->imageEditor()
                            ->maxSize(1024),

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
            ]);
    }

}