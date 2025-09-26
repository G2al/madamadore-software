<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;   // 👈 import
use Illuminate\Database\Eloquent\Builder;      // 👈 import

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Pagamenti';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')
                ->label('Importo (€)')
                ->numeric()
                ->required()
                ->prefix('€')
                ->default(fn () => $this->getOwnerRecord()->costo_lezione),

            Forms\Components\DatePicker::make('date')
                ->label('Data')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
    ->label('Importo')
    ->money('EUR')
    ->summarize([
        // Totale generale
        Sum::make()
            ->label('Totale')
            ->money('EUR'),

        // Totale del mese corrente
        Sum::make()
            ->label('Mese corrente')
            ->money('EUR')
            ->query(function ($query) {   // 👈 tolto type-hint
                $now = now();
                return $query->whereYear('date', $now->year)
                             ->whereMonth('date', $now->month);
            }),
    ]),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
