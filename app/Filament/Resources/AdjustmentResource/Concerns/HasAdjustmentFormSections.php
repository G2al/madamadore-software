<?php

namespace App\Filament\Resources\AdjustmentResource\Concerns;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Resources\AdjustmentResource;

trait HasAdjustmentFormSections
{
    /**
     * Sezione dati cliente
     */
    protected static function clientSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Dati cliente')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome aggiusto')
                    ->required(),

                Forms\Components\TextInput::make('customer_name')
                    ->label('Cliente')
                    ->required(),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Numero di telefono')
                    ->tel(),
            ]);
    }

    /**
     * Sezione pagamenti
     */
    protected static function paymentSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pagamento')
            ->schema([
                Forms\Components\TextInput::make('client_price')
                    ->label('Prezzo cliente')
                    ->numeric()
                    ->required()
                    ->rules(['gte:0']) // non può essere negativo
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => AdjustmentResource::updateCalculations($set, $get)),


                Forms\Components\TextInput::make('deposit')
                    ->label('Acconto')
                    ->numeric()
                    ->default(0) // parte da 0
                    ->rules(['nullable', 'gte:0']) // non obbligatorio, minimo 0
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
