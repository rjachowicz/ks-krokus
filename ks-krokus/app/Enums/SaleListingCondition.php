<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleListingCondition: string
{
    case New = 'new';
    case LikeNew = 'like_new';
    case VeryGood = 'very_good';
    case Good = 'good';
    case Fair = 'fair';
    case ForParts = 'for_parts';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nowy',
            self::LikeNew => 'Jak nowy',
            self::VeryGood => 'Bardzo dobry',
            self::Good => 'Dobry',
            self::Fair => 'Dostateczny',
            self::ForParts => 'Do naprawy lub na części',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $condition) {
            $options[$condition->value] = $condition->label();
        }

        return $options;
    }
}
