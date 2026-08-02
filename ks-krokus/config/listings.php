<?php

declare(strict_types=1);

return [
    'media_disk' => env('MEDIA_DISK', 'public'),
    'image_max_size_kb' => 6144,
    'max_images' => 10,
    'image_max_width' => 1920,
    'image_max_height' => 1920,
    'thumbnail_max_width' => 480,
    'thumbnail_max_height' => 360,
    'expiration_days' => 365,
    'expiration_reminder_days' => 7,
];
