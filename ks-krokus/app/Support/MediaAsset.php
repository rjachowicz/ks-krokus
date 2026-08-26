<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

final class MediaAsset
{
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        try {
            $disk = Storage::disk((string) config('media.disk'));

            return $disk->exists($path) ? $disk->url($path) : null;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public static function firstUrl(?string ...$paths): ?string
    {
        foreach ($paths as $path) {
            $url = self::url($path);

            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }
}
