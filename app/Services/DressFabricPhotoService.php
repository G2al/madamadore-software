<?php

namespace App\Services;

use App\Models\Fabric;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DressFabricPhotoService
{
    public function copyFromInventory(?Fabric $fabric): ?string
    {
        return $this->copyToDirectory($fabric, 'dress-fabrics');
    }

    public function copyToDirectory(?Fabric $fabric, string $directory): ?string
    {
        $sourcePath = $fabric?->image;

        if (blank($sourcePath) || ! Storage::disk('public')->exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
        $targetPath = trim($directory, '/') . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->copy($sourcePath, $targetPath);

        return $targetPath;
    }
}
