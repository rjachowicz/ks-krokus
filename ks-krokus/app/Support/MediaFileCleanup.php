<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class MediaFileCleanup
{
    /** @param list<string|null> $paths */
    public function deleteUnreferenced(array $paths): bool
    {
        $paths = array_values(array_unique(array_filter($paths)));
        $unreferenced = array_values(array_filter(
            $paths,
            fn (string $path): bool => ! $this->isReferenced($path),
        ));

        return $unreferenced === []
            || Storage::disk((string) config('media.disk'))->delete($unreferenced);
    }

    private function isReferenced(string $path): bool
    {
        return DB::table('posts')
            ->where('cover_image_path', $path)
            ->orWhere('cover_variant_path', $path)
            ->exists()
            || DB::table('post_images')
                ->where('path', $path)
                ->orWhere('thumbnail_path', $path)
                ->exists()
            || DB::table('sale_listing_images')
                ->where('path', $path)
                ->orWhere('thumbnail_path', $path)
                ->exists();
    }
}
