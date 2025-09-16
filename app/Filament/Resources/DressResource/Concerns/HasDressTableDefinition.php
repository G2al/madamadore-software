<?php

namespace App\Filament\Resources\DressResource\Concerns;

use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Filters\StatusFilter;
use App\Filament\Filters\CeremonyTypeFilter;
use App\Filament\Filters\DeliveryDateFilter;

trait HasDressTableDefinition
{
    /**
     * Definisce tutte le colonne della tabella Dress.
     *
     * @return array
     */
    protected static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('customer_name')
                ->label('Cliente')
                ->description(fn ($record) => $record->phone_number)
                ->searchable()
                ->sortable()
                ->icon('heroicon-o-user')
                ->weight('bold'),

            Tables\Columns\TextColumn::make('ceremony_type')
                ->label('Cerimonia')
                ->formatStateUsing(fn ($state) => ucfirst($state))
                ->badge()
                ->color('info')
                ->sortable(),

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
                ->color(fn ($record) =>
                    $record?->delivery_date?->isPast()
                        ? 'danger'
                        : 'success'
                )
                ->description(fn ($record) =>
                    $record?->delivery_date?->isPast()
                        ? 'Scaduta'
                        : 'In tempo'
                ),

            Tables\Columns\TextColumn::make('deposit')
                ->label('Acconto')
                ->money('EUR')
                ->color('info')
                ->sortable(),
        ];
    }


    /**
     * Definisce i filtri disponibili nella tabella Dress.
     *
     * @return array
     */
    protected static function tableFilters(): array
{
    return [
        StatusFilter::make(),
        CeremonyTypeFilter::make(),
        DeliveryDateFilter::make(),
        
        // Nuovo filtro scadenze
        Tables\Filters\TernaryFilter::make('upcoming_deliveries')
            ->label('Scadenze')
            ->placeholder('Tutti gli abiti')
            ->trueLabel('In scadenza (3 giorni)')
            ->falseLabel('Non in scadenza')
            ->queries(
                true: fn ($query) => $query->whereBetween('delivery_date', [
                    now(),
                    now()->addDays(3)
                ]),
                false: fn ($query) => $query->where(function ($q) {
                    $q->where('delivery_date', '<', now())
                      ->orWhere('delivery_date', '>', now()->addDays(3))
                      ->orWhereNull('delivery_date');
                }),
            ),
    ];
}

    /**
     * Definisce le azioni disponibili per ogni riga.
     *
     * @return array
     */
protected static function tableRowActions(): array
{
    if (auth()->user()->role === 'staff') {
        // Lo staff vede SOLO il modellino
        return [
            self::getDownloadReceiptAction(),
        ];
    }

    // Admin vede tutto
    return [
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
        self::getTogglePaidAction(),
        self::getDownloadReceiptAction(),
        self::getDownloadContractAction(),
    ];
}


    /**
     * Definisce le bulk actions disponibili nella tabella Dress.
     *
     * @return array
     */
protected static function tableBulkActions(): array
{
    if (auth()->user()->role === 'staff') {
        return []; // niente bulk actions per lo staff
    }

    return [
        Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make(),
        ]),
    ];
}


    /**
     * Costruisce la tabella Dress completa.
     *
     * @param Table $table
     * @return Table
     */
    public static function buildTable(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters(self::tableFilters())
            ->actions(self::tableRowActions())
            ->bulkActions(self::tableBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    private static function isPaid($record): bool
{
    if (!$record) return false;

    $remaining = $record->remaining;
    $total     = (float) $record->total_client_price;

    // Considero "pagato" solo se ho un totale > 0 e il restante è definito e <= 0
    return isset($remaining) && (float) $remaining <= 0 && $total > 0;
}

private static function getTogglePaidAction(): Tables\Actions\Action
{
    return Tables\Actions\Action::make('toggle_paid')
->label(fn($record) => self::isPaid($record) ? 'Rimborso' : 'Salda')
->icon(fn($record) => self::isPaid($record) ? 'heroicon-o-x-circle' : 'heroicon-o-banknotes')
->color(fn($record) => self::isPaid($record) ? 'danger' : 'success')
->action(fn($record) => self::handleTogglePaid($record));

}

    /**
     * Azione per scaricare la ricevuta
     */
private static function getDownloadReceiptAction(): Tables\Actions\Action
{
    return Tables\Actions\Action::make('download_receipt')
        ->label('Modellino')
        ->icon('heroicon-o-document-arrow-down')
        ->color('info')
        ->url(fn($record) => route('pdf.modellino', $record))
        ->openUrlInNewTab();
}

    /**
 * Gestisce il toggle dello stato di pagamento
 */
private static function handleTogglePaid($record): void
{
    if (! self::isPaid($record)) {
        self::settleDress($record);
    } else {
        self::refundDress($record);
    }
}

/**
 * Salda il dress
 */
private static function settleDress($record): void
{
    $record->update(['remaining' => 0]);

    \App\Models\Cashbox::create([
        'type'   => 'income',
        'source' => "Dress #{$record->id}",
        'amount' => $record->total_client_price,
        'note'   => 'Abito saldato',
    ]);
}

/**
 * Rimborsa il dress (torna non saldato)
 */
private static function refundDress($record): void
{
    $totalClientPrice = $record->total_client_price;
    $deposit = $record->deposit;
    $remaining = $totalClientPrice - $deposit;

    $record->update(['remaining' => $remaining]);

    \App\Models\Cashbox::create([
        'type'   => 'expense',
        'source' => "Dress #{$record->id}",
        'amount' => $totalClientPrice,
        'note'   => 'Storno abito - tornato NON saldato',
    ]);
}


private static function getDownloadContractAction(): Tables\Actions\Action
{
    return Tables\Actions\Action::make('download_contract')
        ->label('Preventivo')
        ->icon('heroicon-o-document-text')
        ->color('warning')
        ->url(fn($record) => route('pdf.preventivo', $record))
        ->openUrlInNewTab();
}

}
