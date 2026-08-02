<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleListingReportReason: string
{
    case Outdated = 'outdated';
    case Inappropriate = 'inappropriate';
    case Incorrect = 'incorrect';
    case Abuse = 'abuse';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Outdated => 'Ogłoszenie nieaktualne',
            self::Inappropriate => 'Niewłaściwa treść',
            self::Incorrect => 'Błędne dane',
            self::Abuse => 'Podejrzenie nadużycia',
            self::Other => 'Inny powód',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $reason) {
            $options[$reason->value] = $reason->label();
        }

        return $options;
    }
}
