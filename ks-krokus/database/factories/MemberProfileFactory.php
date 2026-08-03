<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Discipline;
use App\Enums\MemberVerificationStatus;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MemberProfile> */
final class MemberProfileFactory extends Factory
{
    protected $model = MemberProfile::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pzss_license_number' => fake()->unique()->bothify('PZSS-####??'),
            'pzss_license_expires_at' => now()->addYear()->toDateString(),
            'shooting_patent_number' => fake()->unique()->bothify('PAT-#####'),
            'firearm_permit_number' => null,
            'club_member_number' => fake()->unique()->bothify('CZ-####'),
            'joined_club_year' => now()->year - 2,
            'disciplines' => [Discipline::Pistol->value],
            'verification_status' => MemberVerificationStatus::Unverified,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }
}
