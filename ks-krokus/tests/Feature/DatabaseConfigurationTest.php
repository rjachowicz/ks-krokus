<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DatabaseConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_exposes_only_postgresql_connection(): void
    {
        self::assertSame('pgsql', config('database.default'));
        self::assertSame(['pgsql'], array_keys(config('database.connections')));
        self::assertSame('pgsql', config('database.connections.pgsql.driver'));
    }

    public function test_user_dashboard_query_has_a_supporting_index(): void
    {
        $indexes = collect(Schema::getIndexes('event_results'));

        self::assertTrue($indexes->contains(
            static fn (array $index): bool => $index['columns'] === [
                'user_id',
                'deleted_at',
                'created_at',
            ],
        ));
    }
}
