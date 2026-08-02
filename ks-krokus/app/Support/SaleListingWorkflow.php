<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\SaleListingModerationAction;
use App\Enums\SaleListingStatus;
use App\Enums\UserRole;
use App\Models\SaleListing;
use App\Models\User;
use App\Notifications\SaleListingApprovedNotification;
use App\Notifications\SaleListingRejectedNotification;
use App\Notifications\SaleListingSubmittedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

final class SaleListingWorkflow
{
    public function recordCreated(SaleListing $listing, User $actor): void
    {
        $this->record($listing, $actor, SaleListingModerationAction::Created);
    }

    public function recordEdited(SaleListing $listing, User $actor, ?SaleListingStatus $fromStatus = null): void
    {
        $this->record(
            $listing,
            $actor,
            SaleListingModerationAction::Edited,
            $fromStatus,
            $listing->status,
        );
    }

    public function submit(SaleListing $listing, User $actor): void
    {
        $this->guardStatus($listing, [SaleListingStatus::Draft, SaleListingStatus::Rejected]);
        $from = $listing->status;
        $listing->update([
            'status' => SaleListingStatus::Pending,
            'submitted_at' => now(),
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_at' => null,
        ]);
        $this->record($listing, $actor, SaleListingModerationAction::Submitted, $from, $listing->status);

        $administrators = User::query()
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->get();
        Notification::send($administrators, new SaleListingSubmittedNotification($listing));
    }

    public function approve(SaleListing $listing, User $actor): void
    {
        $this->guardStatus($listing, [SaleListingStatus::Pending]);
        $from = $listing->status;
        $publishedAt = now();
        $listing->update([
            'status' => SaleListingStatus::Approved,
            'approved_by' => $actor->getKey(),
            'approved_at' => $publishedAt,
            'published_at' => $publishedAt,
            'expires_at' => $publishedAt->copy()->addDays((int) config('listings.expiration_days')),
            'expiration_reminder_sent_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'sold_at' => null,
            'is_hidden' => false,
        ]);
        $this->record($listing, $actor, SaleListingModerationAction::Approved, $from, $listing->status);
        User::query()->find($listing->user_id)?->notify(
            new SaleListingApprovedNotification($listing),
        );
    }

    public function reject(SaleListing $listing, User $actor, string $reason): void
    {
        $this->guardStatus($listing, [SaleListingStatus::Pending]);
        $from = $listing->status;
        $listing->update([
            'status' => SaleListingStatus::Rejected,
            'rejected_by' => $actor->getKey(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'approved_by' => null,
            'approved_at' => null,
            'published_at' => null,
            'expires_at' => null,
            'expiration_reminder_sent_at' => null,
        ]);
        $this->record($listing, $actor, SaleListingModerationAction::Rejected, $from, $listing->status, $reason);
        User::query()->find($listing->user_id)?->notify(
            new SaleListingRejectedNotification($listing),
        );
    }

    public function markAsSold(SaleListing $listing, User $actor): void
    {
        $this->guardStatus($listing, [SaleListingStatus::Approved]);
        $from = $listing->status;
        $listing->update(['status' => SaleListingStatus::Sold, 'sold_at' => now()]);
        $this->record($listing, $actor, SaleListingModerationAction::Sold, $from, $listing->status);
    }

    public function archive(SaleListing $listing, User $actor): void
    {
        if ($listing->status === SaleListingStatus::Archived) {
            throw ValidationException::withMessages(['status' => 'Ogłoszenie jest już zarchiwizowane.']);
        }

        $from = $listing->status;
        $listing->update(['status' => SaleListingStatus::Archived]);
        $this->record($listing, $actor, SaleListingModerationAction::Archived, $from, $listing->status);
    }

    public function expire(SaleListing $listing): void
    {
        $this->guardStatus($listing, [SaleListingStatus::Approved]);
        $from = $listing->status;
        $listing->update(['status' => SaleListingStatus::Expired]);
        $this->record($listing, null, SaleListingModerationAction::Expired, $from, $listing->status);
    }

    public function hide(SaleListing $listing, User $actor): void
    {
        $listing->update(['is_hidden' => true]);
        $this->record($listing, $actor, SaleListingModerationAction::Hidden);
    }

    public function unhide(SaleListing $listing, User $actor): void
    {
        $listing->update(['is_hidden' => false]);
        $this->record($listing, $actor, SaleListingModerationAction::Unhidden);
    }

    public function flag(SaleListing $listing, User $actor, string $note): void
    {
        $this->record($listing, $actor, SaleListingModerationAction::Flagged, note: $note);
    }

    public function deleted(SaleListing $listing, User $actor): void
    {
        $this->record($listing, $actor, SaleListingModerationAction::Deleted);
    }

    public function restored(SaleListing $listing, User $actor): void
    {
        $this->record($listing, $actor, SaleListingModerationAction::Restored);
    }

    /** @param list<SaleListingStatus> $allowed */
    private function guardStatus(SaleListing $listing, array $allowed): void
    {
        if (! in_array($listing->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Ta operacja nie jest dostępna przy aktualnym statusie ogłoszenia.',
            ]);
        }
    }

    private function record(
        SaleListing $listing,
        ?User $actor,
        SaleListingModerationAction $action,
        ?SaleListingStatus $from = null,
        ?SaleListingStatus $to = null,
        ?string $note = null,
    ): void {
        $listing->moderations()->create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }
}
