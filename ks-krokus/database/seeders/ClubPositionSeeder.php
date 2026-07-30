<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ClubPosition;
use Illuminate\Database\Seeder;

final class ClubPositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['Prezes', 'prezes', 'Kierownictwo i ogólny nadzór nad działalnością klubu.', 10],
            ['Wiceprezes', 'wiceprezes', 'Wsparcie zarządu i nadzór nad wyznaczonymi obszarami działalności.', 20],
            ['Sekretarz', 'sekretarz', 'Sprawy administracyjne i dokumentacja członkowska.', 30],
            ['Skarbnik', 'skarbnik', 'Rozliczenia składek i finanse klubu.', 40],
            ['Komisja Rewizyjna', 'komisja-rewizyjna', 'Organ kontroli wewnętrznej klubu.', 50],
        ];

        foreach ($positions as [$name, $slug, $description, $sortOrder]) {
            ClubPosition::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }
}
