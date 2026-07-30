<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case Competition = 'competition';
    case Training = 'training';

    public function label(): string
    {
        return match ($this) {
            self::Competition => 'Zawody',
            self::Training => 'Trening',
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
