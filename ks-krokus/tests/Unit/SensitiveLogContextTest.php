<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Logging\RedactSensitiveLogContext;
use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

final class SensitiveLogContextTest extends TestCase
{
    public function test_sensitive_values_are_removed_from_nested_log_context(): void
    {
        $handler = new TestHandler(Level::Debug);
        $logger = new Logger('security-test', [$handler]);
        (new RedactSensitiveLogContext)(new IlluminateLogger($logger));

        $logger->warning('Kontrolowany komunikat.', [
            'account_request_id' => 42,
            'password' => 'Sekret123',
            'nested' => [
                'birth_date' => '1990-01-01',
                'pzss_license_number' => 'PZSS-123',
            ],
        ]);

        $records = $handler->getRecords();
        $record = $records[array_key_last($records)];
        self::assertSame(42, $record->context['account_request_id']);
        self::assertSame('[USUNIĘTO]', $record->context['password']);
        self::assertSame('[USUNIĘTO]', $record->context['nested']['birth_date']);
        self::assertSame('[USUNIĘTO]', $record->context['nested']['pzss_license_number']);
    }
}
