<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerResource\Pages;
use App\Filament\Resources\WorkerResource\RelationManagers\AdjustmentItemsRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\CompanyAdjustmentItemsRelationManager;
use App\Models\Worker;
use App\Services\WorkerProductionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $navigationGroup = 'Aggiusti';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Operai / Produzione';
    protected static ?string $modelLabel = 'Operaio';
    protected static ?string $pluralModelLabel = 'Operai';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome operaio')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Toggle::make('active')
                    ->label('Attivo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Operaio')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('today_work_count')
                    ->label('Lavori oggi')
                    ->badge()
                    ->color('info')
                    ->state(fn (Worker $record): int => self::todayProduction($record)['work_count']),

                Tables\Columns\TextColumn::make('today_total_amount')
                    ->label('Totale oggi')
                    ->money('EUR')
                    ->weight('bold')
                    ->color('success')
                    ->state(fn (Worker $record): float => self::todayProduction($record)['total_amount']),

                Tables\Columns\IconColumn::make('active')
                    ->label('Attivo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Attivo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            AdjustmentItemsRelationManager::class,
            CompanyAdjustmentItemsRelationManager::class,
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkers::route('/'),
            'create' => Pages\CreateWorker::route('/create'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }

    private static function todayProduction(Worker $worker): array
    {
        return app(WorkerProductionService::class)->totalsForDate($worker);
    }
}
