<?php

declare(strict_types=1);

namespace App\Enums;

enum IpscDivision: string
{
    case Open = 'open';
    case Standard = 'standard';
    case Classic = 'classic';
    case Production = 'production';
    case ProductionOptics = 'production_optics';
    case Revolver = 'revolver';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Standard => 'Standard',
            self::Classic => 'Classic',
            self::Production => 'Production',
            self::ProductionOptics => 'Production Optics',
            self::Revolver => 'Revolver',
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

    public static function fromStoredValue(?string $value): ?self
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = mb_strtolower(trim($value));

        foreach (self::cases() as $case) {
            if (
                $normalized === mb_strtolower($case->value)
                || $normalized === mb_strtolower($case->label())
            ) {
                return $case;
            }
        }

        return null;
    }

    public static function labelForStoredValue(?string $value): ?string
    {
        return self::fromStoredValue($value)?->label() ?? $value;
    }
}
