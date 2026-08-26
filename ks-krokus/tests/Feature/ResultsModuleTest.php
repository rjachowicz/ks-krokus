<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\IpscDivision;
use App\Enums\MemberAgeCategory;
use App\Enums\PublicationStatus;
use App\Enums\ResultStatus;
use App\Enums\UserRole;
use App\Models\CompetitionDefinition;
use App\Models\EventCompetition;
use App\Models\EventResult;
use App\Models\MemberProfile;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_public_results_can_be_searched_by_participant_first_name(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition] = $this->createCompetition($moderator);
        $this->createResult($eventCompetition, 'Anna Kowalska');
        $this->createResult($eventCompetition, 'Jan Nowak');

        $this->get(route('results.show', [$event, 'q' => 'Anna']))
            ->assertOk()
            ->assertSeeText('Anna Kowalska')
            ->assertDontSeeText('Jan Nowak')
            ->assertSeeText('Liczba dopasowanych wyników: 1');
    }

    public function test_public_results_can_be_searched_by_participant_last_name(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition] = $this->createCompetition($moderator);
        $this->createResult($eventCompetition, 'Anna Kowalska');
        $this->createResult($eventCompetition, 'Jan Nowak');

        $this->get(route('results.show', [$event, 'q' => 'nowak']))
            ->assertOk()
            ->assertSeeText('Jan Nowak')
            ->assertDontSeeText('Anna Kowalska');
    }

    public function test_public_result_search_has_clear_empty_state(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition, $definition] = $this->createCompetition($moderator);
        $this->createResult($eventCompetition, 'Anna Kowalska');

        $this->get(route('results.show', [$event, 'q' => 'Nieistniejący']))
            ->assertOk()
            ->assertSeeText('Liczba dopasowanych wyników: 0')
            ->assertSeeText('Nie znaleziono wyników dla podanego zawodnika.')
            ->assertDontSeeText($definition->name);
    }

    public function test_public_search_preserves_competition_grouping_and_hides_empty_groups(): void
    {
        $moderator = $this->createModerator();
        [$event, $firstCompetition, $firstDefinition] = $this->createCompetition($moderator);
        $secondDefinition = $this->createDefinition('IPSC-PCC-SEARCH', 'IPSC PCC — wyszukiwanie');
        $thirdDefinition = $this->createDefinition('IPSC-RIFLE-SEARCH', 'IPSC Rifle — bez dopasowania');
        $secondCompetition = $event->eventCompetitions()->create([
            'competition_definition_id' => $secondDefinition->id,
        ]);
        $thirdCompetition = $event->eventCompetitions()->create([
            'competition_definition_id' => $thirdDefinition->id,
        ]);
        $this->createResult($firstCompetition, 'Jan Kowalski');
        $this->createResult($secondCompetition, 'Anna Kowalska');
        $this->createResult($thirdCompetition, 'Piotr Nowak');

        $this->get(route('results.show', [$event, 'q' => 'Kowals']))
            ->assertOk()
            ->assertSeeText($firstDefinition->name)
            ->assertSeeText($secondDefinition->name)
            ->assertDontSeeText($thirdDefinition->name)
            ->assertSeeText('Liczba dopasowanych wyników: 2');
    }

    public function test_participant_autocomplete_returns_only_active_users_and_public_snapshot_fields(): void
    {
        $moderator = $this->createModerator();
        $active = User::factory()->create([
            'name' => 'Anna Aktywna',
            'email' => 'anna.private@example.com',
            'phone' => '+48 500 600 700',
            'is_active' => true,
        ]);
        MemberProfile::factory()->create([
            'user_id' => $active,
            'age_category' => MemberAgeCategory::Senior,
        ]);
        User::factory()->create([
            'name' => 'Anna Nieaktywna',
            'is_active' => false,
        ]);

        $response = $this->actingAs($moderator)
            ->getJson(route('admin.results.participants', ['q' => 'Anna']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'Anna Aktywna')
            ->assertJsonPath('data.0.club_name', config('club.short_name'))
            ->assertJsonPath('data.0.category', MemberAgeCategory::Senior->value);

        self::assertArrayNotHasKey('email', $response->json('data.0'));
        self::assertArrayNotHasKey('phone', $response->json('data.0'));
        self::assertArrayNotHasKey('address', $response->json('data.0'));
    }

    public function test_participant_autocomplete_requires_content_management_permission(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.results.participants', ['q' => 'Anna']))
            ->assertForbidden();
    }

    public function test_linked_user_data_is_stored_as_an_immutable_snapshot(): void
    {
        $moderator = $this->createModerator();
        $participant = User::factory()->create([
            'name' => 'Jan Snapshot',
            'is_active' => true,
        ]);
        $profile = MemberProfile::factory()->create([
            'user_id' => $participant,
            'age_category' => MemberAgeCategory::Junior,
        ]);
        [, $eventCompetition] = $this->createCompetition($moderator);

        $this->actingAs($moderator)
            ->post(route('admin.results.store'), [
                'event_competition_id' => $eventCompetition->id,
                'user_id' => $participant->id,
                'participant_name' => 'Sfałszowane imię',
                'club_name' => 'Sfałszowany klub',
                'category' => MemberAgeCategory::Senior->value,
                'score' => '100',
                'status' => ResultStatus::Official->value,
            ])
            ->assertSessionHasNoErrors();

        $result = EventResult::query()->firstOrFail();
        self::assertSame('Jan Snapshot', $result->participant_name);
        self::assertSame(config('club.short_name'), $result->club_name);
        self::assertSame(MemberAgeCategory::Junior->value, $result->category);

        $participant->update(['name' => 'Jan Po Zmianie']);
        $profile->update(['age_category' => MemberAgeCategory::Senior]);

        $result->refresh();
        self::assertSame('Jan Snapshot', $result->participant_name);
        self::assertSame(config('club.short_name'), $result->club_name);
        self::assertSame(MemberAgeCategory::Junior->value, $result->category);
    }

    public function test_external_participant_can_still_be_entered_manually(): void
    {
        $moderator = $this->createModerator();
        [, $eventCompetition] = $this->createCompetition($moderator);

        $this->actingAs($moderator)
            ->post(route('admin.results.store'), [
                'event_competition_id' => $eventCompetition->id,
                'participant_name' => 'Zawodnik Zewnętrzny',
                'club_name' => 'Inny Klub',
                'category' => MemberAgeCategory::Senior->value,
                'score' => '99',
                'status' => ResultStatus::Official->value,
            ])
            ->assertSessionHasNoErrors();

        $result = EventResult::query()->firstOrFail();
        self::assertNull($result->user_id);
        self::assertSame('Zawodnik Zewnętrzny', $result->participant_name);
        self::assertSame('Inny Klub', $result->club_name);
        self::assertSame(MemberAgeCategory::Senior->value, $result->category);
    }

    public function test_ipsc_classification_uses_controlled_division_list(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition] = $this->createCompetition($moderator);

        $this->actingAs($moderator)
            ->post(route('admin.results.store'), [
                'event_competition_id' => $eventCompetition->id,
                'participant_name' => 'Optyczny Zawodnik',
                'classification' => IpscDivision::ProductionOptics->value,
                'score' => '100%',
                'status' => ResultStatus::Official->value,
            ])
            ->assertSessionHasNoErrors();

        self::assertSame(
            IpscDivision::ProductionOptics->value,
            EventResult::query()->firstOrFail()->classification,
        );
        $this->get(route('results.show', $event))
            ->assertOk()
            ->assertSeeText('Production Optics');
    }

    public function test_historical_classification_remains_readable_and_can_be_preserved(): void
    {
        $moderator = $this->createModerator();
        [, $eventCompetition] = $this->createCompetition($moderator);
        $result = $this->createResult($eventCompetition, 'Historyczny Zawodnik', [
            'classification' => 'Legacy Division 2018',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.results.edit', $result))
            ->assertOk()
            ->assertSeeText('Wartość historyczna: Legacy Division 2018');

        $this->put(route('admin.results.update', $result), [
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => $result->participant_name,
            'classification' => 'Legacy Division 2018',
            'score' => '101',
            'status' => ResultStatus::Official->value,
        ])->assertSessionHasNoErrors();

        self::assertSame('Legacy Division 2018', $result->fresh()->classification);
    }

    public function test_new_results_are_available_only_for_published_competitions(): void
    {
        $moderator = $this->createModerator();
        [$publishedEvent, $publishedCompetition] = $this->createCompetition($moderator);
        $definition = $this->createDefinition('IPSC-STATUS', 'IPSC statusy');
        $draftEvent = SportEvent::factory()->create([
            'title' => 'Zawody szkicowe',
            'status' => PublicationStatus::Draft,
        ]);
        $archivedEvent = SportEvent::factory()->create([
            'title' => 'Zawody archiwalne',
            'status' => PublicationStatus::Archived,
        ]);
        $draftCompetition = $draftEvent->eventCompetitions()->create([
            'competition_definition_id' => $definition->id,
        ]);
        $archivedCompetition = $archivedEvent->eventCompetitions()->create([
            'competition_definition_id' => $definition->id,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.results.create'))
            ->assertOk()
            ->assertSeeText($publishedEvent->title)
            ->assertDontSeeText($draftEvent->title)
            ->assertDontSeeText($archivedEvent->title);

        foreach ([$draftCompetition, $archivedCompetition] as $unavailableCompetition) {
            $this->post(route('admin.results.store'), [
                'event_competition_id' => $unavailableCompetition->id,
                'participant_name' => 'Niedozwolony Zawodnik',
                'score' => '100',
                'status' => ResultStatus::Official->value,
            ])->assertSessionHasErrors('event_competition_id');
        }

        self::assertTrue($publishedCompetition->exists);
        self::assertDatabaseCount('event_results', 0);
    }

    public function test_archived_result_update_and_delete_are_blocked_until_republished(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition] = $this->createCompetition($moderator);
        $result = $this->createResult($eventCompetition, 'Archiwalny Zawodnik');
        $event->update(['status' => PublicationStatus::Archived]);

        $this->actingAs($moderator)
            ->get(route('admin.results.edit', $result))
            ->assertOk()
            ->assertSeeText('Wynik archiwalny — tylko do odczytu.')
            ->assertDontSeeText('Zapisz wynik');

        $this->put(route('admin.results.update', $result), [
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => $result->participant_name,
            'score' => '200',
            'status' => ResultStatus::Official->value,
        ])->assertForbidden();
        $this->delete(route('admin.results.destroy', $result))->assertForbidden();
        self::assertSame('100', $result->fresh()->score);
        self::assertFalse($result->fresh()->trashed());

        $event->update(['status' => PublicationStatus::Published]);
        $this->put(route('admin.results.update', $result), [
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => $result->participant_name,
            'score' => '200',
            'status' => ResultStatus::Official->value,
        ])->assertSessionHasNoErrors();
        self::assertSame('200', $result->fresh()->score);
    }

    public function test_admin_result_filters_use_event_competitions_participant_and_status(): void
    {
        $moderator = $this->createModerator();
        [$event, $eventCompetition] = $this->createCompetition($moderator);
        $matching = $this->createResult($eventCompetition, 'Anna Filtrowana', [
            'status' => ResultStatus::Official,
        ]);
        $this->createResult($eventCompetition, 'Jan Pominięty', [
            'status' => ResultStatus::Provisional,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.results.index', [
                'event_id' => $event->id,
                'event_competition_id' => $eventCompetition->id,
                'q' => 'anna',
                'status' => ResultStatus::Official->value,
            ]))
            ->assertOk()
            ->assertSeeText($matching->participant_name)
            ->assertDontSeeText('Jan Pominięty')
            ->assertSee('name="event_competition_id"', false)
            ->assertSee('name="status"', false)
            ->assertDontSee('name="user_id"', false);
    }

    public function test_admin_result_list_eager_loads_event_and_competition_relations(): void
    {
        $moderator = $this->createModerator();
        [, $eventCompetition] = $this->createCompetition($moderator);

        foreach (range(1, 8) as $index) {
            $this->createResult($eventCompetition, "Zawodnik {$index}");
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($moderator)
            ->get(route('admin.results.index'))
            ->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        self::assertLessThanOrEqual(12, $queryCount);
    }

    private function createModerator(): User
    {
        return User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
    }

    private function createDefinition(string $code, string $name): CompetitionDefinition
    {
        return CompetitionDefinition::query()->create([
            'code' => $code,
            'name' => $name,
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'is_active' => true,
            'sort_order' => 20,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function createResult(
        EventCompetition $eventCompetition,
        string $participantName,
        array $attributes = [],
    ): EventResult {
        return EventResult::query()->create([
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => $participantName,
            'score' => '100',
            'status' => ResultStatus::Official,
            ...$attributes,
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
