<?php

namespace App\Filament\Resources\DressProntaMisuraResource\Pages;

use App\Filament\Resources\DressProntaMisuraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDressProntaMisura extends EditRecord
{
    protected static string $resource = DressProntaMisuraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_final_notes')
                ->label('Stampa Note Finali')
                ->icon('heroicon-o-printer')
                ->visible(fn (): bool => filled($this->record->pronta_misura_notes) || filled($this->record->final_notes_photo))
                ->url(fn (): string => route('pdf.dress.final-notes', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('print_secondary_model')
                ->label('Modellino Aggiuntivo')
                ->icon('heroicon-o-photo')
                ->visible(fn (): bool => filled($this->record->secondary_model_image))
                ->url(fn (): string => route('pdf.dress.secondary-model', $this->record))
                ->openUrlInNewTab(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
