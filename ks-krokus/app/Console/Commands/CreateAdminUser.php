<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class CreateAdminUser extends Command
{
    protected $signature = 'club:create-admin
        {--name= : Imię i nazwisko administratora}
        {--email= : Adres e-mail administratora}
        {--password= : Hasło administratora}';

    protected $description = 'Tworzy lub aktualizuje konto administratora KS Krokus.';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Imię i nazwisko'));
        $email = (string) ($this->option('email') ?: $this->ask('Adres e-mail'));
        $password = (string) ($this->option('password') ?: $this->secret('Hasło'));

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::withTrashed()->firstOrNew(['email' => mb_strtolower($email)]);

        if ($user->trashed()) {
            $user->restore();
        }

        $user->fill([
            'name' => $name,
            'email' => mb_strtolower($email),
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'is_active' => true,
        ])->save();

        $this->info("Administrator {$user->email} jest gotowy.");

        return self::SUCCESS;
    }
}
