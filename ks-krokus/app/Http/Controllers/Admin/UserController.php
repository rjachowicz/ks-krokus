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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(
            [
                'q' => ['nullable', 'string', 'max:100'],
                'role' => ['nullable', Rule::enum(UserRole::class)],
                'active' => ['nullable', Rule::in(['0', '1'])],
            ],
            [],
            [
                'q' => 'wyszukiwana fraza',
                'role' => 'rola systemowa',
                'active' => 'status konta',
            ],
        );

        $query = User::query()->latest();

        if (filled($filters['q'] ?? null)) {
            $search = trim((string) $filters['q']);

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if (filled($filters['role'] ?? null)) {
            $query->where('role', $filters['role']);
        }

        if (array_key_exists('active', $filters) && $filters['active'] !== null) {
            $query->where('is_active', $filters['active'] === '1');
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
        $data = $this->normalizeProfileOptions($request, $data);

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

        $data['email'] = mb_strtolower($data['email']);
        $data = $this->normalizeProfileOptions($request, $data);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data, $newIsActive): void {
            $this->guardLastAdmin($user, $data['role'], $newIsActive);
            $user->update($data);
        });

        return back()->with('success', 'Dane użytkownika zostały zapisane.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'user' => 'Nie możesz usunąć własnego konta.',
            ]);
        }

        DB::transaction(function () use ($user): void {
            $this->guardLastAdmin($user, UserRole::User->value, false);
            $user->delete();
        });

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
                ->lockForUpdate()
                ->get(['id'])
                ->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'Nie można usunąć, wyłączyć ani zdegradować ostatniego aktywnego administratora.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeProfileOptions(
        Request $request,
        array $data,
    ): array {
        $data['is_active'] = $request->boolean('is_active');
        $data['is_trainer'] = $request->boolean('is_trainer');
        $data['has_range_access'] = $request->boolean('has_range_access');
        $data['show_email_publicly'] = $request->boolean('show_email_publicly');
        $data['show_phone_publicly'] = $request->boolean('show_phone_publicly');

        if (! $data['is_trainer']) {
            $data['trainer_bio'] = null;
        }

        return $data;
    }
}
