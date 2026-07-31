<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClubPositionRequest;
use App\Models\ClubPosition;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ClubPositionController extends Controller
{
    public function index(): View
    {
        $positions = ClubPosition::query()
            ->with('users')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.positions.index', compact('positions'));
    }

    public function create(): View
    {
        return view('admin.positions.create', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ClubPositionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? [];
        $userSortOrders = $data['user_sort_orders'] ?? [];

        unset($data['user_ids'], $data['user_sort_orders']);

        $data['slug'] = UniqueSlug::for(
            ClubPosition::class,
            $data['slug'] ?: $data['name'],
        );
        $data['is_active'] = $request->boolean('is_active');

        $position = DB::transaction(function () use ($data, $userIds, $userSortOrders): ClubPosition {
            $position = ClubPosition::query()->create($data);
            $this->syncUsers($position, $userIds, $userSortOrders);

            return $position;
        });

        return redirect()
            ->route('admin.positions.edit', $position)
            ->with('success', 'Funkcja klubowa została utworzona.');
    }

    public function edit(ClubPosition $clubPosition): View
    {
        $clubPosition->load('users');

        return view('admin.positions.edit', [
            'position' => $clubPosition,
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        ClubPositionRequest $request,
        ClubPosition $clubPosition,
    ): RedirectResponse {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? [];
        $userSortOrders = $data['user_sort_orders'] ?? [];

        unset($data['user_ids'], $data['user_sort_orders']);

        $data['slug'] = UniqueSlug::for(
            ClubPosition::class,
            $data['slug'] ?: $data['name'],
            $clubPosition->getKey(),
        );
        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($clubPosition, $data, $userIds, $userSortOrders): void {
            $lockedPosition = ClubPosition::query()
                ->whereKey($clubPosition->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $lockedPosition->update($data);
            $this->syncUsers($lockedPosition, $userIds, $userSortOrders);
        });

        return back()->with('success', 'Funkcja klubowa została zapisana.');
    }

    public function destroy(ClubPosition $clubPosition): RedirectResponse
    {
        $clubPosition->delete();

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Funkcja klubowa została usunięta.');
    }

    /**
     * @param  list<int|string>  $userIds
     * @param  array<int|string, int|string|null>  $userSortOrders
     */
    private function syncUsers(
        ClubPosition $position,
        array $userIds,
        array $userSortOrders,
    ): void {
        $syncData = [];

        foreach (array_values($userIds) as $index => $userId) {
            $normalizedUserId = (int) $userId;
            $submittedOrder = $userSortOrders[$normalizedUserId]
                ?? $userSortOrders[(string) $normalizedUserId]
                ?? null;

            $syncData[$normalizedUserId] = [
                'sort_order' => is_numeric($submittedOrder)
                    ? max(0, (int) $submittedOrder)
                    : $index + 1,
            ];
        }

        $position->users()->sync($syncData);
    }
}
