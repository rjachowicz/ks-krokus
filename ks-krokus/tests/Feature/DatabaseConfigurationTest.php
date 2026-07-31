<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class DatabaseConfigurationTest extends TestCase
{
    public function test_application_exposes_only_postgresql_connection(): void
    {
        self::assertSame('pgsql', config('database.default'));
        self::assertSame(['pgsql'], array_keys(config('database.connections')));
        self::assertSame('pgsql', config('database.connections.pgsql.driver'));
    }
}
