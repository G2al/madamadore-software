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

    public static function toPath(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        if (is_string($state)) {
            return $state;
        }

        if (is_array($state)) {
            $first = reset($state);

            return is_string($first) && $first !== '' ? $first : null;
        }

        return null;
    }
}
