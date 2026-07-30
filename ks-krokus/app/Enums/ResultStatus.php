<?php

declare(strict_types=1);

namespace App\Enums;

enum ResultStatus: string
{
    case Provisional = 'provisional';
    case Official = 'official';
    case Disqualified = 'disqualified';

    public function label(): string
    {
        return match ($this) {
            self::Provisional => 'Wstępny',
            self::Official => 'Oficjalny',
            self::Disqualified => 'Dyskwalifikacja',
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
