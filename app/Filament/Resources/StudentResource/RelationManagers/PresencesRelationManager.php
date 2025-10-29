<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\StudentPresence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PresencesRelationManager extends RelationManager
{
    protected static string $relationship = 'presences';
    protected static ?string $title = 'Presenze';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->label('Data lezione')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data lezione')
                    ->date(),

                Tables\Columns\ToggleColumn::make('is_paid')
                    ->label('Pagata')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrata il')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // ✅ Segna selezionate come pagate
                Tables\Actions\BulkAction::make('mark_paid')
                    ->label('Segna come pagate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        DB::transaction(function () use ($records) {
                            StudentPresence::whereIn('id', $records->pluck('id'))
                                ->update(['is_paid' => true]);
                        });
                    })
                    ->after(fn () => \Filament\Notifications\Notification::make()
                        ->title('Lezioni segnate come pagate')
                        ->success()
                        ->send()
                    ),

                // ❌ Segna selezionate come NON pagate
                Tables\Actions\BulkAction::make('mark_unpaid')
                    ->label('Segna come non pagate')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        DB::transaction(function () use ($records) {
                            StudentPresence::whereIn('id', $records->pluck('id'))
                                ->update(['is_paid' => false]);
                        });
                    })
                    ->after(fn () => \Filament\Notifications\Notification::make()
                        ->title('Lezioni segnate come non pagate')
                        ->info()
                        ->send()
                    ),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
