<?php

declare(strict_types=1);

namespace App\Enums;

enum Discipline: string
{
    case Pistol = 'pistol';
    case Rifle = 'rifle';
    case Shotgun = 'shotgun';

    public function label(): string
    {
        return match ($this) {
            self::Pistol => 'Pistolet',
            self::Rifle => 'Karabin',
            self::Shotgun => 'Strzelba',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
