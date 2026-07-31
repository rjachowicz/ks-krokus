<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ClubPosition;
use App\Models\User;
use Database\Seeders\ClubDirectorySeeder;
use Database\Seeders\ClubPositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClubDirectorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_directory_profiles_are_active_and_publicly_visible(): void
    {
        $this->seed(ClubPositionSeeder::class);
        $this->seed(ClubDirectorySeeder::class);

        self::assertTrue(User::query()->trainers()->exists());
        self::assertTrue(User::query()->rangeAccess()->exists());
        self::assertTrue(
            ClubPosition::query()
                ->whereHas(
                    'users',
                    fn ($query) => $query->where('users.is_active', true),
                )
                ->exists(),
        );

        $this->get(route('contact'))
            ->assertOk()
            ->assertSeeText('Dariusz Cichostępski')
            ->assertSeeText('Tadeusz Górka');

        $trainer = User::query()
            ->where('name', 'Tadeusz Górka')
            ->firstOrFail();
        $trainer->update(['is_active' => false]);

        $this->seed(ClubDirectorySeeder::class);

        self::assertTrue($trainer->fresh()->is_active);
    }
}
