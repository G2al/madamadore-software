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
            self::getStatusColumn(),
            self::getRitiratoColumn(),
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
 * Colonna status con badge colorato
 */
private static function getStatusColumn(): Tables\Columns\TextColumn
{
    return Tables\Columns\TextColumn::make('status')
        ->label('Stato')
        ->badge()
        ->formatStateUsing(fn(?string $state) => \App\Models\Adjustment::getStatusLabels()[$state] ?? '-')
        ->color(fn(?string $state) => \App\Models\Adjustment::getStatusColors()[$state] ?? 'gray')
        ->sortable();
}

    /**
     * Colonna ritirato con badge colorato
     */
    private static function getRitiratoColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('ritirato')
            ->label('Ritirato')
            ->badge()
            ->formatStateUsing(fn(bool $state) => $state ? 'SI' : 'NO')
            ->color(fn(bool $state) => $state ? 'success' : 'danger')
            ->icon(fn(bool $state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
            ->sortable();
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
/**
 * Definisce i filtri della tabella
 */
private static function getTableFilters(): array
{
    return [
        // Filtro per stato
        Tables\Filters\SelectFilter::make('status')
            ->label('Stato')
            ->options(\App\Models\Adjustment::getStatusLabels()),

        // Filtro per ritirato
        Tables\Filters\SelectFilter::make('ritirato')
            ->label('Ritirato')
            ->options([
                true => 'SI',
                false => 'NO',
            ]),

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
            self::getCompleteWorkAction(),
            self::getToggleRitiratoAction(),
            self::getDownloadReceiptAction(),
        ];
    }

    /**
     * Azione per completare il lavoro (in_lavorazione → confermato)
     */
    private static function getCompleteWorkAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('complete_work')
            ->label(fn($record) => $record->status === 'in_lavorazione' ? 'Completa Lavoro' : 'Completato')
            ->icon(fn($record) => $record->status === 'in_lavorazione' ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
            ->color(fn($record) => $record->status === 'in_lavorazione' ? 'warning' : 'success')
            ->disabled(fn($record) => $record->status !== 'in_lavorazione')
            ->requiresConfirmation()
            ->modalHeading('Conferma Completamento Lavoro')
            ->modalDescription('Sei sicuro di voler segnare questo aggiusto come completato?')
            ->action(fn($record) => self::handleCompleteWork($record));
    }

    /**
     * Azione per toggle ritirato (quando attivo → stato = consegnato)
     */
    private static function getToggleRitiratoAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('toggle_ritirato')
            ->label(fn($record) => $record->ritirato ? 'Ritirato' : 'Segna Ritirato')
            ->icon(fn($record) => $record->ritirato ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
            ->color(fn($record) => $record->ritirato ? 'success' : 'danger')
            ->requiresConfirmation()
            ->modalHeading(fn($record) => $record->ritirato ? 'Annulla Ritiro' : 'Conferma Ritiro')
            ->modalDescription(fn($record) => $record->ritirato
                ? 'Vuoi annullare il ritiro di questo aggiusto?'
                : 'Confermi che il cliente ha ritirato questo aggiusto? Lo stato diventerà automaticamente "consegnato".')
            ->action(fn($record) => self::handleToggleRitirato($record));
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
            ->url(fn($record) => route('adjustments.receipt', $record)) // usa la route definita
            ->openUrlInNewTab(); // apre subito in scheda browser → da lì la stampi
    }



    /**
     * Gestisce il completamento del lavoro (in_lavorazione → confermato)
     */
    private static function handleCompleteWork(Adjustment $record): void
    {
        if ($record->status === 'in_lavorazione') {
            $record->update(['status' => 'confermato']);
        }
    }

    /**
     * Gestisce il toggle dello stato ritirato
     */
    private static function handleToggleRitirato(Adjustment $record): void
    {
        $newRitiratoStatus = !$record->ritirato;

        $updateData = ['ritirato' => $newRitiratoStatus];

        // Se ritirato diventa TRUE, stato diventa automaticamente 'consegnato'
        if ($newRitiratoStatus === true) {
            $updateData['status'] = 'consegnato';
        }

        $record->update($updateData);
    }

    /**
     * Gestisce il download della ricevuta
     */
    private static function handleDownloadReceipt(Adjustment $record)
    {
        $service = app(AdjustmentReceiptService::class);
        $pdf = $service->generateThermalReceipt($record);

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