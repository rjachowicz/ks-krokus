<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\SaleListingStatus;
use PHPUnit\Framework\TestCase;

final class SaleListingStatusTest extends TestCase
{
    public function test_every_status_has_a_polish_label(): void
    {
        self::assertSame([
            'Szkic',
            'Oczekuje na zatwierdzenie',
            'Zatwierdzone',
            'Odrzucone',
            'Sprzedane',
            'Wygasłe',
            'Zarchiwizowane',
        ], array_map(
            static fn (SaleListingStatus $status): string => $status->label(),
            SaleListingStatus::cases(),
        ));
    }
}
