<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Models\EventResult;
use App\Models\User;

final class EventResultPolicy
{
    public function update(User $user, EventResult $result): bool
    {
        return $user->canManageContent()
            && ! $this->belongsToArchivedEvent($result);
    }

    public function delete(User $user, EventResult $result): bool
    {
        return $this->update($user, $result);
    }

    private function belongsToArchivedEvent(EventResult $result): bool
    {
        return $result->eventCompetition()
            ->whereHas(
                'event',
                fn ($query) => $query->where(
                    'status',
                    PublicationStatus::Archived->value,
                ),
            )
            ->exists();
    }
}
