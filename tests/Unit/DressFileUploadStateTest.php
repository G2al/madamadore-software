<?php

namespace Tests\Unit;

use App\Support\SingleFileUploadState;
use Tests\TestCase;

class DressFileUploadStateTest extends TestCase
{
    public function test_single_file_upload_state_is_normalized_for_filament(): void
    {
        $state = SingleFileUploadState::fromPath('dress-fabrics/example.jpg');

        $this->assertIsArray($state);
        $this->assertCount(1, $state);
        $this->assertSame('dress-fabrics/example.jpg', array_values($state)[0]);
    }

    public function test_single_file_upload_state_returns_null_for_empty_value(): void
    {
        $this->assertNull(SingleFileUploadState::fromPath(null));
    }
}
