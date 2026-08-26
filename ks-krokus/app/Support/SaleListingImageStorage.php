<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;

final class SaleListingImageStorage
{
    public function __construct(
        private readonly MediaImageStorage $storage,
        private readonly MediaFileCleanup $cleanup,
    ) {}

    /**
     * @param  array{x: float, y: float, width: float, height: float}|null  $crop
     * @return array{path: string, thumbnail_path: string, crop: array{x: float, y: float, width: float, height: float}}
     */
    public function store(UploadedFile $file, ?array $crop = null): array
    {
        $stored = $this->storage->store(
            $file,
            'sale-listings/images',
            'sale-listings/thumbnails',
            (int) config('media.thumbnail_width'),
            (int) config('media.thumbnail_height'),
            $crop,
            'images',
        );

        return [
            'path' => $stored['path'],
            'thumbnail_path' => $stored['variant_path'],
            'crop' => $stored['crop'],
        ];
    }

    /**
     * @param  array{x: float, y: float, width: float, height: float}|null  $crop
     * @return array{thumbnail_path: string, crop: array{x: float, y: float, width: float, height: float}}
     */
    public function recrop(string $path, ?array $crop, string $field): array
    {
        $variant = $this->storage->regenerateVariant(
            $path,
            'sale-listings/thumbnails',
            (int) config('media.thumbnail_width'),
            (int) config('media.thumbnail_height'),
            $crop,
            $field,
        );

        return ['thumbnail_path' => $variant['path'], 'crop' => $variant['crop']];
    }

    /** @param list<string|null> $paths */
    public function delete(array $paths): bool
    {
        return $this->cleanup->deleteUnreferenced($paths);
    }
}
