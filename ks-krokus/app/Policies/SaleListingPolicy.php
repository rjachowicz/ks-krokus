<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\SaleListingStatus;
use App\Models\SaleListing;
use App\Models\User;

final class SaleListingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(?User $user, SaleListing $listing): bool
    {
        return $listing->isPubliclyVisible()
            || ($user !== null && (
                $user->getKey() === $listing->user_id
                || $user->canManageContent()
            ));
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, SaleListing $listing): bool
    {
        if ($user->canManageContent()) {
            return true;
        }

        return $user->getKey() === $listing->user_id
            && in_array($listing->status, [
                SaleListingStatus::Draft,
                SaleListingStatus::Rejected,
                SaleListingStatus::Approved,
            ], true);
    }

    public function delete(User $user, SaleListing $listing): bool
    {
        return $user->isAdmin()
            || ($user->getKey() === $listing->user_id
                && $listing->status !== SaleListingStatus::Pending);
    }

    public function submit(User $user, SaleListing $listing): bool
    {
        return $user->getKey() === $listing->user_id
            && in_array($listing->status, [
                SaleListingStatus::Draft,
                SaleListingStatus::Rejected,
            ], true);
    }

    public function approve(User $user, SaleListing $listing): bool
    {
        return $user->isAdmin() && $listing->status === SaleListingStatus::Pending;
    }

    public function reject(User $user, SaleListing $listing): bool
    {
        return $user->isAdmin() && $listing->status === SaleListingStatus::Pending;
    }

    public function markAsSold(User $user, SaleListing $listing): bool
    {
        return $listing->status === SaleListingStatus::Approved
            && ($user->isAdmin() || $user->getKey() === $listing->user_id);
    }

    public function archive(User $user, SaleListing $listing): bool
    {
        return $user->isAdmin() && $listing->status !== SaleListingStatus::Archived;
    }

    public function hide(User $user, SaleListing $listing): bool
    {
        return $user->canManageContent() && ! $listing->is_hidden;
    }

    public function unhide(User $user, SaleListing $listing): bool
    {
        return $user->canManageContent() && $listing->is_hidden;
    }

    public function flag(User $user, SaleListing $listing): bool
    {
        return $user->canManageContent() && ! $user->isAdmin();
    }

    public function restore(User $user, SaleListing $listing): bool
    {
        return $user->isAdmin() && $listing->trashed();
    }
}
