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
