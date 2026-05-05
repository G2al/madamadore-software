<?php

namespace Tests\Unit;

use App\Services\DressTechnicalImageCropService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DressTechnicalImageCropServiceTest extends TestCase
{
    public function test_it_generates_front_detail_crops_from_master_image(): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD non disponibile in questo ambiente di test.');
        }

        Storage::fake('public');

        $sourcePath = 'dress-technical/front/master-front.png';
        $this->createTestImage(Storage::disk('public')->path($sourcePath), 1000, 1600);

        $result = app(DressTechnicalImageCropService::class)->generateFromFront($sourcePath);

        $this->assertGeneratedImage($result['neckline_detail_image'] ?? null, 560, 320);
        $this->assertGeneratedImage($result['sleeve_detail_image'] ?? null, 320, 672);
        $this->assertGeneratedImage($result['bodice_detail_image'] ?? null, 600, 544);
    }

    public function test_it_generates_back_detail_crops_from_master_image(): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD non disponibile in questo ambiente di test.');
        }

        Storage::fake('public');

        $sourcePath = 'dress-technical/back/master-back.png';
        $this->createTestImage(Storage::disk('public')->path($sourcePath), 1000, 1600);

        $result = app(DressTechnicalImageCropService::class)->generateFromBack($sourcePath);

        $this->assertGeneratedImage($result['back_detail_image'] ?? null, 620, 672);
        $this->assertGeneratedImage($result['closure_detail_image'] ?? null, 220, 864);
    }

    private function assertGeneratedImage(?string $relativePath, int $expectedWidth, int $expectedHeight): void
    {
        $this->assertNotNull($relativePath);
        Storage::disk('public')->assertExists($relativePath);

        $size = getimagesize(Storage::disk('public')->path($relativePath));

        $this->assertNotFalse($size);
        $this->assertSame($expectedWidth, $size[0]);
        $this->assertSame($expectedHeight, $size[1]);
    }

    private function createTestImage(string $path, int $width, int $height): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, 255, 255, 255);
        $accent = imagecolorallocate($image, 220, 120, 120);

        imagefilledrectangle($image, 0, 0, $width, $height, $background);
        imagefilledellipse($image, (int) ($width * 0.5), (int) ($height * 0.2), 420, 320, $accent);
        imagefilledrectangle($image, (int) ($width * 0.18), (int) ($height * 0.25), (int) ($width * 0.82), (int) ($height * 0.72), $accent);

        imagepng($image, $path);
        imagedestroy($image);
    }
}
