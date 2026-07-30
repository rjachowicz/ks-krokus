<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', (string) $request->string('role'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => UserRole::options(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['email'] = mb_strtolower($data['email']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_trainer'] = $request->boolean('is_trainer');
        $data['has_range_access'] = $request->boolean('has_range_access');
        $data['show_email_publicly'] = $request->boolean('show_email_publicly');
        $data['show_phone_publicly'] = $request->boolean('show_phone_publicly');

        $user = User::query()->create($data);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Użytkownik został utworzony.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'editedUser' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
    ): RedirectResponse {
        $data = $request->validated();

        $newIsActive = $request->boolean('is_active');

        $this->guardLastAdmin($user, $data['role'], $newIsActive);

        $data['email'] = mb_strtolower($data['email']);
        $data['is_active'] = $newIsActive;
        $data['is_trainer'] = $request->boolean('is_trainer');
        $data['has_range_access'] = $request->boolean('has_range_access');
        $data['show_email_publicly'] = $request->boolean('show_email_publicly');
        $data['show_phone_publicly'] = $request->boolean('show_phone_publicly');

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'Dane użytkownika zostały zapisane.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'user' => 'Nie możesz usunąć własnego konta.',
            ]);
        }

        $this->guardLastAdmin($user, UserRole::User->value, false);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Użytkownik został usunięty.');
    }

    private function guardLastAdmin(
        User $user,
        string $newRole,
        bool $newIsActive,
    ): void {
        $removesActiveAdmin = $user->role === UserRole::Admin
            && $user->is_active
            && (
                $newRole !== UserRole::Admin->value
                || ! $newIsActive
            );

        if (
            $removesActiveAdmin
            && User::query()
                ->where('role', UserRole::Admin->value)
                ->where('is_active', true)
                ->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'Nie można usunąć, wyłączyć ani zdegradować ostatniego aktywnego administratora.',
            ]);
        }
    }
}
