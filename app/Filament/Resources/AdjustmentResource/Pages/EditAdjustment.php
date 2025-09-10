<?php

namespace App\Filament\Resources\AdjustmentResource\Pages;

use App\Filament\Resources\AdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdjustment extends EditRecord
{
    protected static string $resource = AdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('download_receipt')
                ->label('Ricevuta')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->visible(fn ($record) => $record->remaining == 0) // 👈 solo se saldato
                ->action(function ($record) {
                    $service = app(\App\Services\AdjustmentReceiptService::class);
                    $pdf = $service->generateReceipt($record);

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'ricevuta-aggiusto-' . $record->id . '.pdf'
                    );
                }),
        ];
    }
}
