<?php

namespace Tests\Unit;

use App\Filament\Resources\DressResource\Concerns\HasDressFormSections;
use ReflectionMethod;
use Tests\TestCase;

class DressFileUploadStateTest extends TestCase
{
    public function test_single_file_upload_state_is_normalized_for_filament(): void
    {
        $state = $this->invokeFormatter('dress-fabrics/example.jpg');

        $this->assertIsArray($state);
        $this->assertCount(1, $state);
        $this->assertSame('dress-fabrics/example.jpg', array_values($state)[0]);
    }

    public function test_single_file_upload_state_returns_null_for_empty_value(): void
    {
        $this->assertNull($this->invokeFormatter(null));
    }

    private function invokeFormatter(?string $path): ?array
    {
        $class = new class
        {
            use HasDressFormSections;
        };

        $method = new ReflectionMethod($class, 'formatSingleFileUploadState');
        $method->setAccessible(true);

        /** @var ?array $result */
        $result = $method->invoke(null, $path);

        return $result;
    }
}
