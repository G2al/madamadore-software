<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyAdjustmentItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'companyAdjustmentItems';

    protected static ?string $title = 'Aggiusti aziendali lavorati';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('companyAdjustment.customer'))
            ->columns([
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('companyAdjustment.customer.name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Lavoro')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Importo')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('oggi')
                    ->label('Oggi')
                    ->query(fn ($query) => $query->whereDate('completed_at', now()->toDateString())),
            ])
            ->defaultSort('completed_at', 'desc');
    }
}
