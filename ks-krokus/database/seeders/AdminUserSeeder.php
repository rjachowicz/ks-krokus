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
        $name = config('admin.user.name');
        $email = config('admin.user.email');
        $password = config('admin.user.password');

        if (blank($name) || blank($email) || blank($password)) {
            $this->command?->warn(
                'ADMIN_USER_NAME, ADMIN_USER_EMAIL lub ADMIN_USER_PASSWORD nie są ustawione. Konto administratora nie zostało utworzone.',
            );

            return;
        }

        $normalizedEmail = mb_strtolower(trim((string) $email));
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $normalizedEmail],
            [
                'name' => trim((string) $name),
                'password' => Hash::make((string) $password),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }
    }
}
