<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleListingFirearmType: string
{
    case Pistol = 'pistolet';
    case Revolver = 'rewolwer';
    case Rifle = 'karabin';
    case Carbine = 'karabinek';
    case Shotgun = 'strzelba';
    case Airgun = 'wiatrowka';
    case Other = 'inny';

    public function label(): string
    {
        return match ($this) {
            self::Pistol => 'Pistolet',
            self::Revolver => 'Rewolwer',
            self::Rifle => 'Karabin',
            self::Carbine => 'Karabinek',
            self::Shotgun => 'Strzelba',
            self::Airgun => 'Wiatrówka',
            self::Other => 'Inny',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
