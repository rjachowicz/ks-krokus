<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Discipline;
use App\Enums\MemberAgeCategory;
use App\Enums\MemberVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMemberProfileRequest;
use App\Models\AccountRequest;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class MemberProfileController extends Controller
{
    public function edit(User $user): View
    {
        $profile = MemberProfile::query()
            ->where('user_id', $user->getKey())
            ->first();
        Gate::authorize($profile ? 'view' : 'create', $profile ?? MemberProfile::class);

        $profile?->load('verifier');
        $sourceRequest = AccountRequest::query()
            ->where('created_user_id', $user->getKey())
            ->latest('reviewed_at')
            ->first();

        return view('admin.member-profiles.edit', [
            'editedUser' => $user,
            'profile' => $profile,
            'sourceRequest' => $sourceRequest,
            'disciplines' => Discipline::options(),
            'ageCategories' => MemberAgeCategory::options(),
            'verificationStatuses' => MemberVerificationStatus::options(),
        ]);
    }

    public function update(UpdateMemberProfileRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $isVerified = $data['verification_status'] === MemberVerificationStatus::Verified->value;

        $data['disciplines'] = $data['disciplines'] ?? [];
        $data['verified_at'] = $isVerified ? now() : null;
        $data['verified_by'] = $isVerified ? $request->user()->getKey() : null;

        DB::transaction(function () use ($user, $data): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            MemberProfile::query()->updateOrCreate(
                ['user_id' => $user->getKey()],
                $data,
            );
        });

        return back()->with('success', 'Dane członkowskie zostały zapisane.');
    }
}
