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
                        ->label('Telefono')
                        ->tel(),
                ]),

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
                ->offColor('danger'),

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
                ->closeOnDateSelection(),

            // Repeater per gli aggiusti
            Forms\Components\Repeater::make('items')
                ->label('Aggiusti')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome aggiusto')
                        ->placeholder('es. Pantalone nero, Camicia bianca...')
                        ->default('')
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
}