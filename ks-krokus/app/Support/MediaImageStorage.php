<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class MediaImageStorage
{
    /**
     * @param  array{x: float, y: float, width: float, height: float}|null  $crop
     * @return array{path: string, variant_path: string, crop: array{x: float, y: float, width: float, height: float}}
     */
    public function store(
        UploadedFile $file,
        string $directory,
        string $variantDirectory,
        int $variantWidth,
        int $variantHeight,
        ?array $crop,
        string $field,
    ): array {
        $fullPath = null;
        $variantPath = null;

        try {
            $contents = file_get_contents($file->getRealPath());

            if (! is_string($contents) || $contents === '') {
                throw new \RuntimeException('Nie można odczytać obrazu.');
            }

            $source = $this->decode($contents, $file->getRealPath());
            $full = $this->resizeWithin(
                $source,
                (int) config('media.full_max_width'),
                (int) config('media.full_max_height'),
            );
            imagedestroy($source);

            $extension = $this->outputExtension($file->getMimeType());
            $fullPath = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
            $this->put($fullPath, $this->encode($full, $extension));

            [$variant, $normalizedCrop] = $this->cropVariant(
                $full,
                $variantWidth,
                $variantHeight,
                $crop,
                $field,
            );
            $variantPath = trim($variantDirectory, '/').'/'.Str::uuid().'.'.$extension;
            $this->put($variantPath, $this->encode($variant, $extension));
            imagedestroy($variant);
            imagedestroy($full);

            return [
                'path' => $fullPath,
                'variant_path' => $variantPath,
                'crop' => $normalizedCrop,
            ];
        } catch (ValidationException $exception) {
            $this->deletePartial([$fullPath, $variantPath]);

            throw $exception;
        } catch (Throwable $exception) {
            $this->deletePartial([$fullPath, $variantPath]);
            report($exception);

            throw ValidationException::withMessages([
                $field => 'Nie udało się przetworzyć i zapisać zdjęcia. Spróbuj ponownie później.',
            ]);
        }
    }

    /**
     * @param  array{x: float, y: float, width: float, height: float}|null  $crop
     * @return array{path: string, crop: array{x: float, y: float, width: float, height: float}}
     */
    public function regenerateVariant(
        string $sourcePath,
        string $variantDirectory,
        int $variantWidth,
        int $variantHeight,
        ?array $crop,
        string $field,
    ): array {
        $variantPath = null;

        try {
            $contents = Storage::disk((string) config('media.disk'))->get($sourcePath);
            $source = $this->decode($contents);
            [$variant, $normalizedCrop] = $this->cropVariant(
                $source,
                $variantWidth,
                $variantHeight,
                $crop,
                $field,
            );
            $extension = $this->extensionForStoredImage($sourcePath, $contents);
            $variantPath = trim($variantDirectory, '/').'/'.Str::uuid().'.'.$extension;
            $this->put($variantPath, $this->encode($variant, $extension));
            imagedestroy($variant);
            imagedestroy($source);

            return ['path' => $variantPath, 'crop' => $normalizedCrop];
        } catch (ValidationException $exception) {
            $this->deletePartial([$variantPath]);

            throw $exception;
        } catch (Throwable $exception) {
            $this->deletePartial([$variantPath]);
            report($exception);

            throw ValidationException::withMessages([
                $field => 'Nie można wygenerować nowego kadru. Sprawdź, czy plik źródłowy nadal istnieje.',
            ]);
        }
    }

    private function decode(string $contents, ?string $orientationPath = null): mixed
    {
        $image = imagecreatefromstring($contents);

        if ($image === false) {
            throw new \RuntimeException('Plik nie zawiera prawidłowego obrazu.');
        }

        $orientation = $orientationPath !== null
            ? $this->readOrientation($orientationPath)
            : 1;
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

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function readOrientation(string $path): int
    {
        if (! function_exists('exif_read_data')) {
            return 1;
        }

        try {
            $exif = @exif_read_data($path);

            return is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        } catch (Throwable) {
            return 1;
        }
    }

    private function resizeWithin(mixed $source, int $maxWidth, int $maxHeight): mixed
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $target = $this->canvas($width, $height);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        return $target;
    }

    /**
     * @param  array{x: float, y: float, width: float, height: float}|null  $crop
     * @return array{0: mixed, 1: array{x: float, y: float, width: float, height: float}}
     */
    private function cropVariant(
        mixed $source,
        int $targetWidth,
        int $targetHeight,
        ?array $crop,
        string $field,
    ): array {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $crop ??= MediaCrop::centered($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);

        if (! MediaCrop::isValid($crop)) {
            throw ValidationException::withMessages([$field => 'Współrzędne kadru są nieprawidłowe.']);
        }

        $x = (int) round($crop['x'] * $sourceWidth);
        $y = (int) round($crop['y'] * $sourceHeight);
        $width = (int) round($crop['width'] * $sourceWidth);
        $height = (int) round($crop['height'] * $sourceHeight);

        if (
            $width < 1
            || $height < 1
            || $x < 0
            || $y < 0
            || ($x + $width) > $sourceWidth
            || ($y + $height) > $sourceHeight
        ) {
            throw ValidationException::withMessages([$field => 'Kadr wykracza poza rzeczywiste wymiary zdjęcia.']);
        }

        $actualAspect = $width / $height;
        $targetAspect = $targetWidth / $targetHeight;

        $aspectTolerance = max(0.03, 2 / min($width, $height));

        if (abs($actualAspect - $targetAspect) / $targetAspect > $aspectTolerance) {
            throw ValidationException::withMessages([$field => 'Proporcje kadru są nieprawidłowe. Ustaw kadr ponownie.']);
        }

        $target = $this->canvas($targetWidth, $targetHeight);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $x,
            $y,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        return [$target, $crop];
    }

    private function canvas(int $width, int $height): mixed
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        return $canvas;
    }

    private function outputExtension(?string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => function_exists('imagewebp') ? 'webp' : 'jpg',
            default => 'jpg',
        };
    }

    private function extensionForStoredImage(string $path, string $contents): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return $this->outputExtension((new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: null);
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

    private function put(string $path, string $contents): void
    {
        if (! Storage::disk((string) config('media.disk'))->put($path, $contents)) {
            throw new \RuntimeException('Dysk mediów odrzucił zapis.');
        }
    }

    /** @param list<string|null> $paths */
    private function deletePartial(array $paths): void
    {
        $paths = array_values(array_filter($paths));

        if ($paths !== []) {
            Storage::disk((string) config('media.disk'))->delete($paths);
        }
    }
}
