<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Models\SportEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SportEvent> */
final class SportEventFactory extends Factory
{
    protected $model = SportEvent::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(4),
            'event_type' => EventType::Competition,
            'description' => fake()->paragraph(),
            'start_at' => now()->addDays(7),
            'end_at' => now()->addDays(7)->addHours(6),
            'location_name' => 'Strzelnica KS Krokus',
            'status' => PublicationStatus::Published,
            'is_public' => true,
            'email_reminders_enabled' => true,
        ];
    }
}
