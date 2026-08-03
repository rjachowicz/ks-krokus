<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MemberProfile;
use App\Models\User;

final class MemberProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, MemberProfile $memberProfile): bool
    {
        return $user->isAdmin() || $user->getKey() === $memberProfile->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, MemberProfile $memberProfile): bool
    {
        return $user->isAdmin();
    }
}
