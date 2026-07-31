<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn(
                'ADMIN_EMAIL lub ADMIN_PASSWORD nie są ustawione. Konto administratora nie zostało utworzone.',
            );

            return;
        }

        $normalizedEmail = mb_strtolower(trim((string) $email));
        $user = User::withTrashed()->firstOrNew(['email' => $normalizedEmail]);

        if ($user->trashed()) {
            $user->restore();
        }

        $user->fill([
            'name' => trim((string) env('ADMIN_NAME', 'Administrator KS Krokus')),
            'email' => $normalizedEmail,
            'password' => Hash::make((string) $password),
            'role' => UserRole::Admin,
            'is_active' => true,
        ])->save();
    }
}
