<?php

namespace App\Filament\Resources\SpecialDressResource\Concerns;

use App\Models\SpecialDress;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

trait HasSpecialDressTableDefinition
{
    protected static function tableColumns(): array
    {
        $columns = [
            Tables\Columns\TextColumn::make('customer_name')
                ->label('Cliente')
                ->description(fn (SpecialDress $record) => $record->phone_number)
                ->searchable()
                ->sortable()
                ->icon('heroicon-o-user')
                ->weight('bold'),

            Tables\Columns\TextColumn::make('ceremony_type')
                ->label('Festività')
                ->badge()
                ->color('info')
                ->sortable(),

            Tables\Columns\TextColumn::make('character')
                ->label('Personaggio/Maschera')
                ->searchable()
                ->sortable()
                ->placeholder('-')
                ->toggleable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Stato')
                ->badge()
                ->formatStateUsing(fn (?string $state) => self::getStatusLabels()[$state] ?? '-')
                ->color(fn (?string $state) => self::getStatusColors()[$state] ?? 'gray')
                ->sortable(),

            Tables\Columns\TextColumn::make('delivery_date')
                ->label('Consegna')
                ->date('d/m/Y')
                ->sortable()
                ->color(fn (SpecialDress $record) =>
                    $record?->delivery_date?->isPast() ? 'danger' : 'success'
                )
                ->description(fn (SpecialDress $record) =>
                    $record?->delivery_date?->isPast() ? 'Scaduta' : 'In tempo'
                ),
        ];

        if (auth()->user()?->role === 'admin') {
            $columns[] = Tables\Columns\TextColumn::make('deposit')
                ->label('Acconto')
                ->money('EUR')
                ->color('info')
                ->sortable();

            $columns[] = Tables\Columns\TextColumn::make('total_client_price')
                ->label('Prezzo')
                ->money('EUR')
                ->color('success')
                ->sortable();
        }

        return $columns;
    }

    protected static function tableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label('Stato')
                ->options(self::getStatusLabels()),

            Tables\Filters\TernaryFilter::make('upcoming_deliveries')
                ->label('Scadenze')
                ->placeholder('Tutti')
                ->trueLabel('In scadenza (3 giorni)')
                ->falseLabel('Non in scadenza')
                ->queries(
                    true: fn (Builder $q) => $q->whereBetween('delivery_date', [now(), now()->addDays(3)]),
                    false: fn (Builder $q) => $q->where(function (Builder $qq) {
                        $qq->where('delivery_date', '<', now())
                           ->orWhere('delivery_date', '>', now()->addDays(3))
                           ->orWhereNull('delivery_date');
                    }),
                ),
        ];
    }

    protected static function tableRowActions(): array
    {
        return [

            Tables\Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->role === 'admin'),


            // ✅ Modellino (PDF)
        \Filament\Tables\Actions\Action::make('download_receipt')
            ->label('Modellino')
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->url(fn (\App\Models\SpecialDress $record) => route('pdf.special.modellino', $record))
            ->openUrlInNewTab()
            ->visible(fn () => auth()->user()?->role === 'admin'),

        // ✅ Preventivo (PDF)
        \Filament\Tables\Actions\Action::make('download_contract')
            ->label('Preventivo')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->url(fn (\App\Models\SpecialDress $record) => route('pdf.special.preventivo', $record))
            ->openUrlInNewTab()
            ->visible(fn () => auth()->user()?->role === 'admin'),

            Tables\Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->role === 'admin')
                ->requiresConfirmation()
                ->modalHeading('Elimina abito')
                ->modalDescription('Operazione definitiva: l\'abito verrà eliminato dal database.'),

            Tables\Actions\Action::make('download_receipt')
                ->label('Modellino')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->visible(false),
        ];
    }

    protected static function tableBulkActions(): array
    {
        if (auth()->user()?->role === 'staff') {
            return [];
        }

        // ✅ Solo eliminazione massiva, nessuna azione "Cestino/Archivio"
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->requiresConfirmation()
                    ->modalHeading('Elimina selezionati')
                    ->modalDescription('Operazione definitiva: gli abiti selezionati verranno eliminati dal database.')
                    ->deselectRecordsAfterCompletion(),
            ]),
        ];
    }

    public static function buildTable(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters(self::tableFilters())
            ->actions(self::tableRowActions())
            ->bulkActions(self::tableBulkActions())
            ->groups([
                Tables\Grouping\Group::make('ceremony_type')
                    ->label('Festività')
                    ->collapsible(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('ceremony_type');
    }
}
