<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SaleListingImageStorage
{
    /** @return array{path: string, thumbnail_path: string|null} */
    public function store(UploadedFile $file): array
    {
        $disk = Storage::disk(config('listings.media_disk'));

        try {
            $originalPath = $file->store('sale-listings/originals', config('listings.media_disk'));
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'images' => 'Nie udało się zapisać zdjęcia. Spróbuj ponownie później.',
            ]);
        }

        if (! is_string($originalPath) || $originalPath === '') {
            throw ValidationException::withMessages([
                'images' => 'Nie udało się zapisać przesłanego zdjęcia.',
            ]);
        }

        if (! function_exists('imagecreatefromstring')) {
            return ['path' => $originalPath, 'thumbnail_path' => null];
        }

        $optimizedPath = null;
        $thumbnailPath = null;

        try {
            $contents = file_get_contents($file->getRealPath());
            $image = is_string($contents) ? imagecreatefromstring($contents) : false;

            if ($image === false) {
                return ['path' => $originalPath, 'thumbnail_path' => null];
            }

            $image = $this->correctOrientation($image, $file);
            $optimized = $this->resizeWithin(
                $image,
                (int) config('listings.image_max_width'),
                (int) config('listings.image_max_height'),
            );
            $thumbnail = $this->resizeWithin(
                $image,
                (int) config('listings.thumbnail_max_width'),
                (int) config('listings.thumbnail_max_height'),
            );
            $extension = $this->outputExtension($file->getMimeType());
            $optimizedPath = 'sale-listings/images/'.Str::uuid().'.'.$extension;
            $thumbnailPath = 'sale-listings/thumbnails/'.Str::uuid().'.'.$extension;
            $optimizedContents = $this->encode($optimized, $extension);
            $thumbnailContents = $this->encode($thumbnail, $extension);

            if (! $disk->put($optimizedPath, $optimizedContents)) {
                throw new \RuntimeException('Nie zapisano zoptymalizowanego obrazu.');
            }

            if (! $disk->put($thumbnailPath, $thumbnailContents)) {
                $thumbnailPath = null;
            }

            imagedestroy($image);
            if ($optimized !== $image) {
                imagedestroy($optimized);
            }
            if ($thumbnail !== $image && $thumbnail !== $optimized) {
                imagedestroy($thumbnail);
            }

            $disk->delete($originalPath);

            return ['path' => $optimizedPath, 'thumbnail_path' => $thumbnailPath];
        } catch (Throwable $exception) {
            report($exception);
            $disk->delete(array_values(array_filter([$optimizedPath, $thumbnailPath])));

            return ['path' => $originalPath, 'thumbnail_path' => null];
        }
    }

    /** @param list<string|null> $paths */
    public function delete(array $paths): bool
    {
        $paths = array_values(array_unique(array_filter($paths)));

        return $paths === []
            || Storage::disk(config('listings.media_disk'))->delete($paths);
    }

    private function correctOrientation(mixed $image, UploadedFile $file): mixed
    {
        if (
            $file->getMimeType() !== 'image/jpeg'
            || ! function_exists('exif_read_data')
        ) {
            return $image;
        }

        try {
            $exif = @exif_read_data($file->getRealPath());
            $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
            $angle = match ($orientation) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            if ($angle === 0) {
                return $image;
            }

            $rotated = imagerotate($image, $angle, 0);

            if ($rotated !== false) {
                imagedestroy($image);

                return $rotated;
            }
        } catch (Throwable) {
            // Metadane EXIF są opcjonalne; uszkodzone dane nie blokują uploadu.
        }

        return $image;
    }

    private function resizeWithin(mixed $source, int $maxWidth, int $maxHeight): mixed
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);

        if ($scale >= 1) {
            return $source;
        }

        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        return $target;
    }

    private function outputExtension(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => function_exists('imagewebp') ? 'webp' : 'jpg',
            default => 'jpg',
        };
    }

    private function encode(mixed $image, string $extension): string
    {
        ob_start();

        $success = match ($extension) {
            'png' => imagepng($image, null, 7),
            'webp' => imagewebp($image, null, 84),
            default => imagejpeg($image, null, 86),
        };
        $contents = ob_get_clean();

        if (! $success || ! is_string($contents)) {
            throw new \RuntimeException('Nie udało się zakodować obrazu.');
        }

        return $contents;
    }
}
