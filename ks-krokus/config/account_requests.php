<?php

declare(strict_types=1);

return [
    'retention_months' => (int) env('ACCOUNT_REQUEST_RETENTION_MONTHS', 12),
    'retention_action' => env('ACCOUNT_REQUEST_RETENTION_ACTION', 'anonymize'),
];
