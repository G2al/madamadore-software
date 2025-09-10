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
            Forms\Components\TextInput::make('name')
                ->label('Nome aggiusto')
                ->required(),

            Forms\Components\Select::make('customer_id')
                ->label('Cliente')
                ->relationship('customer', 'name')
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, Set $set) {
                    // Quando cambio cliente, aggiorno il telefono visualizzato
                    $set('customer_phone', \App\Models\Customer::find($state)?->phone_number);
                })
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome cliente')
                        ->required(),
                    Forms\Components\TextInput::make('phone_number')
                        ->label('Telefono')
                        ->tel(),
                ]),

            // Telefono di sola lettura con icona WhatsApp nel suffix
            Forms\Components\TextInput::make('customer_phone')
                ->label('Telefono cliente')
                ->readOnly()              // (più leggibile di disabled)
                ->dehydrated(false)       // non salvare in adjustments
                ->afterStateHydrated(function (
                    \Filament\Forms\Components\TextInput $component,
                    $state,
                    $record
                ) {
                    // All'apertura della pagina valorizzo dal record esistente
                    $component->state($record?->customer?->phone_number);
                })
                ->suffixIcon('heroicon-o-chat-bubble-left-right')
                ->suffixIconColor('success')
                ->extraAttributes(function (Get $get) {
                    // Preparo link wa.me solo se ho un numero valido
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
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection(),
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
                    ->required()
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
                    ->readOnly(),

                Forms\Components\TextInput::make('remaining')
                    ->label('Rimanente')
                    ->numeric()
                    ->readOnly(),

                Forms\Components\TextInput::make('profit')
                    ->label('Guadagno')
                    ->numeric()
                    ->readOnly(),
            ]);
    }
}
