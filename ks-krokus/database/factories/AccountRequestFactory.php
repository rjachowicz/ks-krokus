<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountRequestStatus;
use App\Enums\Discipline;
use App\Models\AccountRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AccountRequest> */
final class AccountRequestFactory extends Factory
{
    protected $model = AccountRequest::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+48 '.fake()->numerify('### ### ###'),
            'birth_date' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'pzss_license_number' => fake()->unique()->bothify('PZSS-####??'),
            'pzss_license_expires_at' => now()->addYear()->toDateString(),
            'patent_number' => fake()->unique()->bothify('PAT-#####'),
            'firearm_permit_number' => null,
            'member_number' => null,
            'joined_year' => now()->year - 2,
            'disciplines' => [Discipline::Pistol->value],
            'additional_information' => null,
            'data_processing_consent' => true,
            'status' => AccountRequestStatus::Pending,
        ];
    }
}
