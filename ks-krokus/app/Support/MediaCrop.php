<?php

declare(strict_types=1);

namespace App\Support;

final class MediaCrop
{
    /**
     * @param  mixed  $value
     * @return array{x: float, y: float, width: float, height: float}|null
     */
    public static function fromInput(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        foreach (['x', 'y', 'width', 'height'] as $key) {
            if (! isset($value[$key]) || ! is_numeric($value[$key])) {
                return null;
            }
        }

        return [
            'x' => (float) $value['x'],
            'y' => (float) $value['y'],
            'width' => (float) $value['width'],
            'height' => (float) $value['height'],
        ];
    }

    /** @param array{x: float, y: float, width: float, height: float} $crop */
    public static function isValid(array $crop): bool
    {
        return $crop['x'] >= 0
            && $crop['y'] >= 0
            && $crop['width'] > 0
            && $crop['height'] > 0
            && $crop['x'] <= 1
            && $crop['y'] <= 1
            && $crop['width'] <= 1
            && $crop['height'] <= 1
            && ($crop['x'] + $crop['width']) <= 1.000001
            && ($crop['y'] + $crop['height']) <= 1.000001;
    }

    /** @return array{x: float, y: float, width: float, height: float} */
    public static function centered(int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight): array
    {
        $sourceAspect = $sourceWidth / $sourceHeight;
        $targetAspect = $targetWidth / $targetHeight;

        if ($sourceAspect > $targetAspect) {
            $width = $targetAspect / $sourceAspect;

            return ['x' => (1 - $width) / 2, 'y' => 0.0, 'width' => $width, 'height' => 1.0];
        }

        $height = $sourceAspect / $targetAspect;

        return ['x' => 0.0, 'y' => (1 - $height) / 2, 'width' => 1.0, 'height' => $height];
    }
}
