<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleListingStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Sold = 'sold';
    case Expired = 'expired';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Szkic',
            self::Pending => 'Oczekuje na zatwierdzenie',
            self::Approved => 'Zatwierdzone',
            self::Rejected => 'Odrzucone',
            self::Sold => 'Sprzedane',
            self::Expired => 'Wygasłe',
            self::Archived => 'Zarchiwizowane',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Approved => 'admin-badge--success',
            self::Pending => 'admin-badge--warning',
            self::Rejected => 'admin-badge--danger',
            self::Sold, self::Expired, self::Archived => 'admin-badge--muted',
            self::Draft => '',
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
