<?php

namespace App\Filament\Resources\AdjustmentResource\Concerns;

use App\Models\Adjustment;
use App\Models\Cashbox;
use App\Services\AdjustmentReceiptService;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

trait HasAdjustmentTableDefinition
{
    public static function buildTable(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->actions(self::getTableActions())
            ->bulkActions(self::getBulkActions())
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Definisce le colonne della tabella
     */
    private static function getTableColumns(): array
    {
        return [
            self::getCustomerColumn(),
            self::getAdjustmentNameColumn(),
            self::getClientPriceColumn(),
            self::getDepositColumn(),
            self::getRemainingColumn(),
            self::getProfitColumn(),
            self::getDeliveryDateColumn(),
        ];
    }

    /**
     * Colonna cliente con nome e telefono
     */
    private static function getCustomerColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('customer.name')
            ->label('Cliente')
            ->description(fn($record) => $record->customer?->phone_number)
            ->searchable()
            ->sortable()
            ->icon('heroicon-o-user')
            ->weight('bold');
    }

    /**
     * Colonna nome aggiusto
     */
private static function getAdjustmentNameColumn(): Tables\Columns\TextColumn
{
    return Tables\Columns\TextColumn::make('items_display')
        ->label('Aggiusti')
        ->searchable(query: function ($query, $search) {
            return $query->whereHas('items', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        })
        ->state(function ($record) {
            $items = $record->items;
            $count = $items->count();
            
            if ($count === 0) {
                return 'Nessun aggiusto';
            }
            
            $firstName = $items->first()->name;
            
            if ($count === 1) {
                return $firstName;
            }
            
            return $firstName . " + " . ($count - 1) . " altri";
        })
        ->badge()
        ->color('info')
        ->tooltip(function ($record) {
            return $record->items->pluck('name')->join(', ');
        });
}
    /**
     * Colonna prezzo cliente
     */
    private static function getClientPriceColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('client_price')
            ->label('Prezzo')
            ->money('EUR')
            ->sortable()
            ->color('gray');
    }

    /**
     * Colonna acconto (nascosta di default)
     */
    private static function getDepositColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('deposit')
            ->label('Acconto')
            ->money('EUR')
            ->color('info')
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Colonna rimanente con badge colorato
     */
    private static function getRemainingColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('remaining')
            ->label('Rimanente')
            ->money('EUR')
            ->sortable()
            ->badge()
            ->color(fn($state) => $state > 0 ? 'danger' : 'success')
            ->icon(fn($state) => $state > 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle');
    }

    /**
     * Colonna guadagno (nascosta di default)
     */
    private static function getProfitColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('profit')
            ->label('Guadagno')
            ->money('EUR')
            ->weight('bold')
            ->color('warning')
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Colonna data consegna con stato scadenza
     */
    private static function getDeliveryDateColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('delivery_date')
            ->label('Consegna')
            ->date('d/m/Y')
            ->sortable()
            ->badge()
            ->color(fn($record) => self::getDeliveryDateColor($record))
            ->description(fn($record) => self::getDeliveryDateDescription($record))
            ->icon(fn($record) => self::getDeliveryDateIcon($record));
    }

    /**
     * Determina il colore del badge per la data di consegna
     */
    private static function getDeliveryDateColor($record): string
    {
        return $record?->delivery_date && Carbon::parse($record->delivery_date)->isPast()
            ? 'danger'
            : 'success';
    }

    /**
     * Determina la descrizione per la data di consegna
     */
    private static function getDeliveryDateDescription($record): string
    {
        return $record?->delivery_date && Carbon::parse($record->delivery_date)->isPast()
            ? 'Scaduta'
            : 'In tempo';
    }

    /**
     * Determina l'icona per la data di consegna
     */
    private static function getDeliveryDateIcon($record): string
    {
        return $record?->delivery_date && Carbon::parse($record->delivery_date)->isPast()
            ? 'heroicon-o-clock'
            : 'heroicon-o-check-circle';
    }

    /**
     * Definisce i filtri della tabella
     */
    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('saldato')
                ->label('Saldato')
                ->query(fn($query) => $query->where('remaining', '=', 0)),

            Tables\Filters\Filter::make('non_saldato')
                ->label('Non saldato')
                ->query(fn($query) => $query->where('remaining', '>', 0)),

            Tables\Filters\SelectFilter::make('customer_id')
                ->label('Cliente')
                ->relationship('customer', 'name'),
        ];
    }

    /**
     * Definisce le azioni della tabella
     */
    private static function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            self::getTogglePaidAction(),
            self::getDownloadReceiptAction(),
        ];
    }

    /**
     * Azione per saldare/rimborsare l'aggiusto
     */
    private static function getTogglePaidAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('toggle_paid')
            ->label(fn($record) => $record->remaining > 0 ? 'Salda' : 'Rimborso')
            ->icon(fn($record) => $record->remaining > 0 ? 'heroicon-o-banknotes' : 'heroicon-o-x-circle')
            ->color(fn($record) => $record->remaining > 0 ? 'success' : 'danger')
            ->requiresConfirmation()
            ->action(fn($record) => self::handleTogglePaid($record));
    }

    /**
     * Azione per scaricare la ricevuta
     */
    private static function getDownloadReceiptAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('download_receipt')
            ->label('Ricevuta')
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->action(fn($record) => self::handleDownloadReceipt($record));
    }

    /**
     * Gestisce il toggle dello stato di pagamento
     */
    private static function handleTogglePaid(Adjustment $record): void
    {
        if ($record->remaining > 0) {
            self::settleAdjustment($record);
        } else {
            self::refundAdjustment($record);
        }
    }

    /**
     * Salda l'aggiusto
     */
    private static function settleAdjustment(Adjustment $record): void
    {
        $record->update(['remaining' => 0]);

        Cashbox::create([
            'type'   => 'income',
            'source' => "Adjustment #{$record->id}",
            'amount' => $record->total,
            'note'   => 'Aggiusto saldato',
        ]);
    }

    /**
     * Rimborsa l'aggiusto (torna non saldato)
     */
    private static function refundAdjustment(Adjustment $record): void
    {
        $record->update(['remaining' => $record->total]);

        Cashbox::create([
            'type'   => 'expense',
            'source' => "Adjustment #{$record->id}",
            'amount' => $record->total,
            'note'   => 'Storno aggiusto - tornato NON saldato',
        ]);
    }

    /**
     * Gestisce il download della ricevuta
     */
    private static function handleDownloadReceipt(Adjustment $record)
    {
        $service = app(AdjustmentReceiptService::class);
        $pdf = $service->generateReceipt($record);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "ricevuta-aggiusto-{$record->id}.pdf"
        );
    }

    /**
     * Definisce le azioni bulk
     */
    private static function getBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }
}