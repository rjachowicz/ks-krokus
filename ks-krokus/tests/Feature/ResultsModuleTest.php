<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Enums\ResultStatus;
use App\Enums\UserRole;
use App\Models\CompetitionDefinition;
use App\Models\EventCompetition;
use App\Models\EventResult;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ResultsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_results_render_competition_definition(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition, $definition] = $this->createCompetition($moderator);

        EventResult::query()->create([
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => 'Jan Testowy',
            'score' => '245.14',
            'place' => 1,
            'status' => ResultStatus::Official,
            'entered_by' => $moderator->id,
        ]);

        self::assertTrue($eventCompetition->competition->is($definition));

        $this->get(route('results.index'))
            ->assertOk()
            ->assertSeeText($event->title);

        $this->get(route('results.show', $event))
            ->assertOk()
            ->assertSeeText($definition->name)
            ->assertSeeText('Jan Testowy')
            ->assertSeeText('245.14');
    }

    public function test_result_management_and_linked_user_dashboard_render(): void
    {
        $moderator = $this->createModerator();
        $participant = User::factory()->create([
            'role' => UserRole::User,
            'is_active' => true,
        ]);
        [, $eventCompetition, $definition] = $this->createCompetition($moderator);

        $result = EventResult::query()->create([
            'event_competition_id' => $eventCompetition->id,
            'user_id' => $participant->id,
            'participant_name' => $participant->name,
            'score' => '98%',
            'status' => ResultStatus::Provisional,
            'entered_by' => $moderator->id,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.results.index'))
            ->assertOk()
            ->assertSeeText($definition->name);

        $this->get(route('admin.results.create'))
            ->assertOk()
            ->assertSeeText($definition->name);

        $this->get(route('admin.results.edit', $result))
            ->assertOk()
            ->assertSeeText($definition->name);

        $this->actingAs($participant)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText($definition->name)
            ->assertSeeText('98%');
    }

    public function test_moderator_can_create_update_and_remove_result(): void
    {
        $moderator = $this->createModerator();
        [, $eventCompetition] = $this->createCompetition($moderator);

        $this->actingAs($moderator)
            ->post(route('admin.results.store'), [
                'event_competition_id' => $eventCompetition->id,
                'participant_name' => 'Anna Testowa',
                'score' => '100',
                'status' => ResultStatus::Official->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $result = EventResult::query()->firstOrFail();

        $this->put(route('admin.results.update', $result), [
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => 'Anna Testowa',
            'score' => '101',
            'place' => 2,
            'status' => ResultStatus::Official->value,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        self::assertSame('101', $result->fresh()->score);
        self::assertSame(2, $result->fresh()->place);

        $this->delete(route('admin.results.destroy', $result))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.results.index'));

        $this->assertSoftDeleted($result);
    }

    private function createModerator(): User
    {
        return User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
    }

    /**
     * @return array{SportEvent, EventCompetition, CompetitionDefinition}
     */
    private function createCompetition(User $creator): array
    {
        $definition = CompetitionDefinition::query()->create([
            'code' => 'IPSC-HG-RESULTS',
            'name' => 'IPSC Handgun — test wyników',
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $event = SportEvent::query()->create([
            'title' => 'Zawody modułu wyników',
            'slug' => 'zawody-modulu-wynikow',
            'event_type' => EventType::Competition,
            'start_at' => now()->subDay(),
            'location_name' => 'Strzelnica testowa',
            'status' => PublicationStatus::Published,
            'is_public' => true,
            'created_by' => $creator->id,
        ]);

        $eventCompetition = $event->eventCompetitions()->create([
            'competition_definition_id' => $definition->id,
        ]);

        return [$event, $eventCompetition, $definition];
    }
}
