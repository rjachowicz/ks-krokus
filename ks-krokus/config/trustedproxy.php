<?php

declare(strict_types=1);

$proxies = trim((string) env('TRUSTED_PROXIES', ''));

return [
    'proxies' => $proxies !== '' ? $proxies : null,
];
