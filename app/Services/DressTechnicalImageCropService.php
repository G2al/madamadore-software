<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DressTechnicalImageCropService
{
    private const COMBINED_FRONT_CROP = ['x' => 0.04, 'y' => 0.05, 'w' => 0.42, 'h' => 0.90];

    private const COMBINED_BACK_CROP = ['x' => 0.54, 'y' => 0.05, 'w' => 0.42, 'h' => 0.90];

    private const FRONT_CROPS = [
        'neckline_detail_image' => ['x' => 0.22, 'y' => 0.07, 'w' => 0.56, 'h' => 0.20],
        'sleeve_detail_image' => ['x' => 0.00, 'y' => 0.20, 'w' => 0.32, 'h' => 0.42],
        'bodice_detail_image' => ['x' => 0.20, 'y' => 0.16, 'w' => 0.60, 'h' => 0.34],
    ];

    private const BACK_CROPS = [
        'back_detail_image' => ['x' => 0.19, 'y' => 0.08, 'w' => 0.62, 'h' => 0.42],
        'closure_detail_image' => ['x' => 0.39, 'y' => 0.02, 'w' => 0.22, 'h' => 0.54],
    ];

    public function generateFromFront(?string $relativePath): array
    {
        return $this->generateCrops($relativePath, self::FRONT_CROPS);
    }

    public function generateFromBack(?string $relativePath): array
    {
        return $this->generateCrops($relativePath, self::BACK_CROPS);
    }

    public function generateAll(?string $frontRelativePath, ?string $backRelativePath): array
    {
        return array_merge(
            $this->generateFromFront($frontRelativePath),
            $this->generateFromBack($backRelativePath),
        );
    }

    public function generateFromTechnicalDrawing(?string $relativePath): array
    {
        $result = [
            'front_view_image' => null,
            'back_view_image' => null,
            'neckline_detail_image' => null,
            'sleeve_detail_image' => null,
            'bodice_detail_image' => null,
            'back_detail_image' => null,
            'closure_detail_image' => null,
        ];

        if (! $this->isAvailable()) {
            return $result;
        }

        if (blank($relativePath) || ! Storage::disk('public')->exists($relativePath)) {
            return $result;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $imageInfo = $this->openImage($absolutePath);

        if ($imageInfo === null) {
            return $result;
        }

        try {
            $frontPath = $this->cropAndStore(
                sourceImage: $imageInfo['resource'],
                sourceWidth: $imageInfo['width'],
                sourceHeight: $imageInfo['height'],
                mimeType: $imageInfo['mime'],
                field: 'front_view_image',
                crop: self::COMBINED_FRONT_CROP,
                targetDirectory: 'dress-technical/front/auto',
                filenamePrefix: 'front-view',
            );

            $backPath = $this->cropAndStore(
                sourceImage: $imageInfo['resource'],
                sourceWidth: $imageInfo['width'],
                sourceHeight: $imageInfo['height'],
                mimeType: $imageInfo['mime'],
                field: 'back_view_image',
                crop: self::COMBINED_BACK_CROP,
                targetDirectory: 'dress-technical/back/auto',
                filenamePrefix: 'back-view',
            );
        } finally {
            imagedestroy($imageInfo['resource']);
        }

        $result['front_view_image'] = $frontPath;
        $result['back_view_image'] = $backPath;

        return array_merge(
            $result,
            $this->generateAll($frontPath, $backPath),
        );
    }

    private function generateCrops(?string $relativePath, array $definitions): array
    {
        $result = array_fill_keys(array_keys($definitions), null);

        if (! $this->isAvailable()) {
            return $result;
        }

        if (blank($relativePath) || ! Storage::disk('public')->exists($relativePath)) {
            return $result;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $imageInfo = $this->openImage($absolutePath);

        if ($imageInfo === null) {
            return $result;
        }

        try {
            foreach ($definitions as $field => $definition) {
                $result[$field] = $this->cropAndStore(
                    sourceImage: $imageInfo['resource'],
                    sourceWidth: $imageInfo['width'],
                    sourceHeight: $imageInfo['height'],
                    mimeType: $imageInfo['mime'],
                    field: $field,
                    crop: $definition,
                );
            }
        } finally {
            imagedestroy($imageInfo['resource']);
        }

        return $result;
    }

    public function isAvailable(): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled');
    }

    private function openImage(string $absolutePath): ?array
    {
        $raw = @file_get_contents($absolutePath);

        if ($raw === false) {
            return null;
        }

        $resource = @imagecreatefromstring($raw);

        if ($resource === false) {
            return null;
        }

        $size = @getimagesize($absolutePath);

        if ($size === false || empty($size[0]) || empty($size[1]) || empty($size['mime'])) {
            imagedestroy($resource);

            return null;
        }

        return [
            'resource' => $resource,
            'width' => (int) $size[0],
            'height' => (int) $size[1],
            'mime' => (string) $size['mime'],
        ];
    }

    private function cropAndStore(
        \GdImage $sourceImage,
        int $sourceWidth,
        int $sourceHeight,
        string $mimeType,
        string $field,
        array $crop,
        string $targetDirectory = 'dress-technical/details/auto',
        ?string $filenamePrefix = null,
    ): ?string {
        $x = max(0, (int) round($sourceWidth * $crop['x']));
        $y = max(0, (int) round($sourceHeight * $crop['y']));
        $width = max(1, (int) round($sourceWidth * $crop['w']));
        $height = max(1, (int) round($sourceHeight * $crop['h']));

        if ($x + $width > $sourceWidth) {
            $width = $sourceWidth - $x;
        }

        if ($y + $height > $sourceHeight) {
            $height = $sourceHeight - $y;
        }

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $target = imagecreatetruecolor($width, $height);

        if ($target === false) {
            return null;
        }

        try {
            $this->prepareCanvasForMime($target, $mimeType);
            imagecopyresampled(
                $target,
                $sourceImage,
                0,
                0,
                $x,
                $y,
                $width,
                $height,
                $width,
                $height,
            );

            $relativePath = sprintf(
                '%s/%s-%s.%s',
                trim($targetDirectory, '/'),
                $filenamePrefix ?? str_replace('_image', '', $field),
                Str::uuid(),
                $this->extensionForMime($mimeType),
            );

            Storage::disk('public')->makeDirectory(trim($targetDirectory, '/'));
            $absoluteTargetPath = Storage::disk('public')->path($relativePath);

            $this->saveImage($target, $absoluteTargetPath, $mimeType);

            return $relativePath;
        } finally {
            imagedestroy($target);
        }
    }

    private function prepareCanvasForMime(\GdImage $image, string $mimeType): void
    {
        if (in_array($mimeType, ['image/png', 'image/webp', 'image/gif'], true)) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
        } else {
            $background = imagecolorallocate($image, 255, 255, 255);
            imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $background);
        }
    }

    private function saveImage(\GdImage $image, string $absolutePath, string $mimeType): void
    {
        $saved = match ($mimeType) {
            'image/png' => imagepng($image, $absolutePath, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $absolutePath, 90) : imagejpeg($image, $absolutePath, 92),
            'image/gif' => imagegif($image, $absolutePath),
            default => imagejpeg($image, $absolutePath, 92),
        };

        if ($saved === false) {
            throw new RuntimeException('Impossibile salvare il ritaglio tecnico automatico.');
        }
    }

    private function extensionForMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => function_exists('imagewebp') ? 'webp' : 'jpg',
            'image/gif' => 'gif',
            default => 'jpg',
        };
    }
}
