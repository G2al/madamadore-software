<?php

namespace App\Filament\Resources\DressResource\Pages;

use App\Filament\Resources\DressResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateDress extends CreateRecord
{
    protected static string $resource = DressResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['drawing_pad'])) {
            $data['drawing_image'] = $this->storePadAsImage($data['drawing_pad']);
        }

        // Non persistiamo lo state del pad
        unset($data['drawing_pad']);

        return $data;
    }

    private function storePadAsImage(string $dataUrl): string
    {
        // Supporta sia PNG che JPEG se mai cambiassi export
        if (!preg_match('#^data:image/(png|jpe?g);base64,#i', $dataUrl)) {
            return '';
        }

        $binary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl));
        $ext = str_contains($dataUrl, 'image/jpeg') || str_contains($dataUrl, 'image/jpg') ? 'jpg' : 'png';

        $path = 'dress-drawings/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
