<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingCondition;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingStatus;
use App\Models\SaleListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SaleListing> */
class SaleListingFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 999999),
            'category' => fake()->randomElement(SaleListingCategory::cases()),
            'firearm_type' => fake()->randomElement(SaleListingFirearmType::cases()),
            'manufacturer' => fake()->company(),
            'model' => strtoupper(fake()->bothify('??-###')),
            'caliber' => fake()->randomElement(['9×19 mm', '.22 LR', '.223 Rem']),
            'condition' => fake()->randomElement(SaleListingCondition::cases()),
            'year_of_manufacture' => fake()->numberBetween(1980, (int) now()->year),
            'price' => fake()->randomFloat(2, 50, 10000),
            'price_negotiable' => fake()->boolean(),
            'description' => fake()->paragraphs(2, true),
            'location' => 'Nowy Sącz',
            'contact_name' => fake()->firstName(),
            'contact_phone' => '+48 600 000 000',
            'contact_email' => fake()->safeEmail(),
            'show_phone' => false,
            'show_email' => false,
            'status' => SaleListingStatus::Draft,
            'is_hidden' => false,
            'view_count' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => SaleListingStatus::Pending,
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => SaleListingStatus::Approved,
            'submitted_at' => now()->subDay(),
            'approved_at' => now(),
            'published_at' => now(),
            'expires_at' => now()->addDays((int) config('listings.expiration_days')),
        ]);
    }
}
