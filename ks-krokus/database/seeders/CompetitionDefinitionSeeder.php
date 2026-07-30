<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Models\CompetitionDefinition;
use Illuminate\Database\Seeder;

final class CompetitionDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['ISSF-P10', 'Pistolet pneumatyczny 10 m', Discipline::Pistol, CompetitionSystem::ISSF, 10],
            ['ISSF-P25', 'Pistolet sportowy 25 m', Discipline::Pistol, CompetitionSystem::ISSF, 20],
            ['ISSF-P25-RF', 'Pistolet szybkostrzelny 25 m', Discipline::Pistol, CompetitionSystem::ISSF, 30],
            ['ISSF-P50', 'Pistolet dowolny 50 m', Discipline::Pistol, CompetitionSystem::ISSF, 40],
            ['ISSF-K10', 'Karabin pneumatyczny 10 m', Discipline::Rifle, CompetitionSystem::ISSF, 50],
            ['ISSF-K50-3P', 'Karabin 50 m, trzy postawy', Discipline::Rifle, CompetitionSystem::ISSF, 60],
            ['ISSF-K50-L', 'Karabin 50 m, leżąc', Discipline::Rifle, CompetitionSystem::ISSF, 70],
            ['ISSF-TRAP', 'Trap', Discipline::Shotgun, CompetitionSystem::ISSF, 80],
            ['ISSF-SKEET', 'Skeet', Discipline::Shotgun, CompetitionSystem::ISSF, 90],
            ['ISSF-DTRAP', 'Double Trap', Discipline::Shotgun, CompetitionSystem::ISSF, 100],
            ['IPSC-HG', 'IPSC Handgun', Discipline::Pistol, CompetitionSystem::IPSC, 110],
            ['IPSC-PCC', 'IPSC PCC', Discipline::Rifle, CompetitionSystem::IPSC, 120],
            ['IPSC-RIFLE', 'IPSC Rifle', Discipline::Rifle, CompetitionSystem::IPSC, 130],
            ['IPSC-SHOTGUN', 'IPSC Shotgun', Discipline::Shotgun, CompetitionSystem::IPSC, 140],
        ];

        foreach ($definitions as [$code, $name, $discipline, $system, $sortOrder]) {
            CompetitionDefinition::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'discipline' => $discipline,
                    'competition_system' => $system,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ],
            );
        }
    }
}
