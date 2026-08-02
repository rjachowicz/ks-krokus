<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleListingCategory: string
{
    case Handgun = 'bron-krotka';
    case LongGun = 'bron-dluga';
    case Shotgun = 'strzelba';
    case Airgun = 'bron-pneumatyczna';
    case Optics = 'optyka';
    case Accessories = 'akcesoria';
    case Equipment = 'wyposazenie';
    case Parts = 'czesci';
    case Other = 'inne';

    public function label(): string
    {
        return match ($this) {
            self::Handgun => 'Broń krótka',
            self::LongGun => 'Broń długa',
            self::Shotgun => 'Strzelba',
            self::Airgun => 'Broń pneumatyczna',
            self::Optics => 'Optyka',
            self::Accessories => 'Akcesoria',
            self::Equipment => 'Wyposażenie',
            self::Parts => 'Części',
            self::Other => 'Inne',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $category) {
            $options[$category->value] = $category->label();
        }

        return $options;
    }
}
