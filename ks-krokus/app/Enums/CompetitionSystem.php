<?php

declare(strict_types=1);

namespace App\Enums;

enum CompetitionSystem: string
{
    case ISSF = 'issf';
    case IPSC = 'ipsc';

    public function label(): string
    {
        return strtoupper($this->value);
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
