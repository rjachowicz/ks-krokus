<?php

declare(strict_types=1);

namespace App\Enums;

enum MemberVerificationStatus: string
{
    case Unverified = 'unverified';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Niezweryfikowane',
            self::Verified => 'Zweryfikowane',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Unverified => 'status-badge--warning',
            self::Verified => 'status-badge--success',
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
