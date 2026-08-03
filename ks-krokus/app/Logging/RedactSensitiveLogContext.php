<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Logger;
use Monolog\LogRecord;

final class RedactSensitiveLogContext
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'authorization',
        'birth_date',
        'club_member_number',
        'contact_email',
        'contact_name',
        'contact_phone',
        'cookie',
        'current_password',
        'email',
        'firearm_permit_number',
        'first_name',
        'internal_notes',
        'last_name',
        'member_number',
        'message',
        'password',
        'password_confirmation',
        'patent_number',
        'phone',
        'pzss_license_expires_at',
        'pzss_license_number',
        'rejection_reason',
        'remember_token',
        'shooting_patent_number',
        'token',
    ];

    public function __invoke(IlluminateLogger $logger): void
    {
        $monolog = $logger->getLogger();

        if (! $monolog instanceof Logger) {
            return;
        }

        foreach ($monolog->getHandlers() as $handler) {
            $handler->pushProcessor(
                fn (LogRecord $record): LogRecord => $record->with(
                    context: $this->redact($record->context),
                    extra: $this->redact($record->extra),
                ),
            );
        }
    }

    /**
     * @param  array<mixed>  $values
     * @return array<mixed>
     */
    private function redact(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_string($key) && $this->isSensitiveKey($key)) {
                $values[$key] = '[USUNIĘTO]';

                continue;
            }

            if (is_array($value)) {
                $values[$key] = $this->redact($value);
            }
        }

        return $values;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = mb_strtolower(str_replace(['-', '.'], '_', $key));

        return in_array($normalized, self::SENSITIVE_KEYS, true)
            || str_contains($normalized, 'password')
            || str_contains($normalized, 'token')
            || str_contains($normalized, 'license');
    }
}
