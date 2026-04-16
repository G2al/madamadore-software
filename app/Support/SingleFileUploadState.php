<?php

namespace App\Support;

use Illuminate\Support\Str;

class SingleFileUploadState
{
    public static function fromPath(?string $path): ?array
    {
        if (blank($path)) {
            return null;
        }

        return [(string) Str::uuid() => $path];
    }
}
