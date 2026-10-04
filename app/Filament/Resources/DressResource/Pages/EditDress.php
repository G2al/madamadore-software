<?php

namespace App\Filament\Resources\DressResource\Pages;

use App\Filament\Resources\DressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EditDress extends EditRecord
{
    protected static string $resource = DressResource::class;

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
            Actions\DeleteAction::make(),
        ];
    }
protected function mutateFormDataBeforeSave(array $data): array
{
    if (!empty($data['drawing_pad'])) {
        $data['drawing_image'] = $this->storePadAsImage($data['drawing_pad']);
    }
    unset($data['drawing_pad']); // non salvarlo in DB
    return $data;
}

private function storePadAsImage(string $dataUrl): string
{
    $binary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl));
    $ext = str_contains($dataUrl, 'image/jpeg') || str_contains($dataUrl, 'image/jpg') ? 'jpg' : 'png';

    $path = 'dress-drawings/' . Str::uuid() . '.' . $ext;
    Storage::disk('public')->put($path, $binary);

    return $path;
}

}
