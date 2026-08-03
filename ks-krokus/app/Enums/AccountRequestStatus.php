<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Oczekuje na weryfikację',
            self::Approved => 'Zatwierdzony',
            self::Rejected => 'Odrzucony',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'status-badge--warning',
            self::Approved => 'status-badge--success',
            self::Rejected => 'status-badge--danger',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_column(
            array_map(
                static fn (self $status): array => [$status->value, $status->label()],
                self::cases(),
            ),
            1,
            0,
        );
    }
}
