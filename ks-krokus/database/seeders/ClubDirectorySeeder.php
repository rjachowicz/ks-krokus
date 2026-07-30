<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ClubPosition;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ClubDirectorySeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, mixed> $contacts */
        $contacts = config('contacts', []);

        $positionAssignments = [];

        foreach ($contacts['board'] ?? [] as $member) {
            $user = $this->upsertPerson(
                name: $member['name'],
                email: $member['email'] ?? null,
                phone: $member['phone'] ?? null,
                showEmail: true,
                showPhone: filled($member['phone'] ?? null),
            );

            $slug = match (mb_strtoupper((string) $member['role'])) {
                'PREZES' => 'prezes',
                'SEKRETARZ' => 'sekretarz',
                'SKARBNIK' => 'skarbnik',
                default => null,
            };

            if ($slug !== null) {
                $positionAssignments[$slug][] = $user->getKey();
            }
        }

        foreach ($contacts['vice_presidents'] ?? [] as $member) {
            $user = $this->upsertPerson(
                name: $member['name'],
                email: $member['email'] ?? null,
                phone: $member['phone'] ?? null,
                showEmail: true,
                showPhone: true,
            );

            $positionAssignments['wiceprezes'][] = $user->getKey();
        }

        foreach ($contacts['audit_committee']['members'] ?? [] as $member) {
            $user = $this->upsertPerson(
                name: $member['name'],
                email: null,
                phone: null,
                showEmail: false,
                showPhone: false,
            );

            $positionAssignments['komisja-rewizyjna'][] = $user->getKey();
        }

        foreach ($contacts['trainers'] ?? [] as $member) {
            $user = $this->upsertPerson(
                name: $member['name'],
                email: null,
                phone: $member['phone'] ?? null,
                showEmail: false,
                showPhone: true,
            );

            $user->update(['is_trainer' => true]);
        }

        foreach ($contacts['range_access_people'] ?? [] as $member) {
            $user = $this->upsertPerson(
                name: $member['name'],
                email: null,
                phone: $member['phone'] ?? null,
                showEmail: false,
                showPhone: true,
            );

            $user->update(['has_range_access' => true]);
        }

        foreach ($positionAssignments as $slug => $userIds) {
            $position = ClubPosition::query()->where('slug', $slug)->first();

            if ($position === null) {
                continue;
            }

            $syncData = [];

            foreach (array_values(array_unique($userIds)) as $index => $userId) {
                $syncData[$userId] = ['sort_order' => $index + 1];
            }

            $position->users()->syncWithoutDetaching($syncData);
        }
    }

    private function upsertPerson(
        string $name,
        ?string $email,
        ?string $phone,
        bool $showEmail,
        bool $showPhone,
    ): User {
        $realEmail = filled($email)
            ? mb_strtolower((string) $email)
            : null;

        $query = User::withTrashed();

        $user = $realEmail !== null
            ? $query->where('email', $realEmail)->first()
            : $query->where('name', $name)->first();

        if ($user === null) {
            $profileEmail = $realEmail
                ?: 'profile+'.Str::slug($name).'@ks-krokus.local';

            $user = User::query()->create([
                'name' => $name,
                'email' => $profileEmail,
                'password' => Hash::make(Str::random(64)),
                'role' => UserRole::User,
                'phone' => $phone,
                'is_active' => false,
                'show_email_publicly' => $showEmail && $realEmail !== null,
                'show_phone_publicly' => $showPhone && filled($phone),
            ]);

            return $user;
        }

        if ($user->trashed()) {
            $user->restore();
        }

        $user->fill([
            'name' => $name,
            'phone' => $user->phone ?: $phone,
            'show_email_publicly' => $user->show_email_publicly || ($showEmail && $realEmail !== null),
            'show_phone_publicly' => $user->show_phone_publicly || ($showPhone && filled($phone)),
        ])->save();

        return $user;
    }
}
