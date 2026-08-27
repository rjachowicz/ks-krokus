<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Enums\ResultStatus;
use App\Enums\UserRole;
use App\Models\ClubPosition;
use App\Models\CompetitionDefinition;
use App\Models\EventCompetition;
use App\Models\EventResult;
use App\Models\Post;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminCrudAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_crud_and_public_preview_respect_publication_date(): void
    {
        $moderator = $this->moderator();

        $this->actingAs($moderator)
            ->get(route('admin.posts.index'))
            ->assertOk();
        $this->get(route('admin.posts.create'))->assertOk();

        $response = $this->post(route('admin.posts.store'), [
            'title' => 'Audytowana aktualność',
            'content' => 'Treść aktualności.',
            'status' => PublicationStatus::Draft->value,
        ])->assertSessionHasNoErrors();

        $post = Post::query()->firstOrFail();

        $response->assertRedirect(route('admin.posts.index'));
        $this->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSeeText($post->title);

        $futurePublication = now()->addDay()->format('Y-m-d H:i:s');

        $this->put(route('admin.posts.update', $post), [
            'title' => 'Zmieniona aktualność',
            'content' => 'Zmieniona treść.',
            'status' => PublicationStatus::Published->value,
            'published_at' => $futurePublication,
        ])->assertSessionHasNoErrors();

        $post->refresh();
        self::assertSame('Zmieniona aktualność', $post->title);
        $this->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertDontSee('Podgląd publiczny');
        $this->get(route('news.show', $post))->assertNotFound();

        $this->delete(route('admin.posts.destroy', $post))
            ->assertRedirect(route('admin.posts.index'));
        $this->assertSoftDeleted($post);
    }

    public function test_event_crud_keeps_linked_inactive_competition_and_rejects_new_one(): void
    {
        $moderator = $this->moderator();
        $linkedDefinition = $this->definition('AUDIT-EVENT-LINKED');

        $this->actingAs($moderator)
            ->get(route('admin.events.index'))
            ->assertOk();
        $this->get(route('admin.events.create'))->assertOk();

        $response = $this->post(route('admin.events.store'), [
            'title' => 'Audytowane zawody',
            'event_type' => EventType::Competition->value,
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'location_name' => 'Strzelnica testowa',
            'status' => PublicationStatus::Draft->value,
            'is_public' => '1',
            'competition_ids' => [$linkedDefinition->id],
        ])->assertSessionHasNoErrors();

        $event = SportEvent::query()->firstOrFail();

        $response->assertRedirect(route('admin.events.edit', $event));
        $this->assertDatabaseHas('event_competitions', [
            'sport_event_id' => $event->id,
            'competition_definition_id' => $linkedDefinition->id,
        ]);

        $linkedDefinition->update(['is_active' => false]);

        $this->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSeeText($linkedDefinition->name)
            ->assertSeeText('Dodaj wynik');

        $unlinkedInactiveDefinition = $this->definition(
            'AUDIT-EVENT-INACTIVE',
            false,
        );

        $this->put(route('admin.events.update', $event), [
            'title' => 'Niedozwolona zmiana',
            'event_type' => EventType::Competition->value,
            'start_at' => $event->start_at->format('Y-m-d H:i:s'),
            'location_name' => $event->location_name,
            'status' => PublicationStatus::Draft->value,
            'is_public' => '1',
            'competition_ids' => [
                $linkedDefinition->id,
                $unlinkedInactiveDefinition->id,
            ],
        ])->assertSessionHasErrors('competition_ids.1');

        $this->put(route('admin.events.update', $event), [
            'title' => 'Zmienione zawody',
            'event_type' => EventType::Competition->value,
            'start_at' => $event->start_at->format('Y-m-d H:i:s'),
            'location_name' => $event->location_name,
            'status' => PublicationStatus::Published->value,
            'is_public' => '1',
            'competition_ids' => [$linkedDefinition->id],
        ])->assertSessionHasNoErrors();

        self::assertSame('Zmienione zawody', $event->fresh()->title);

        $this->delete(route('admin.events.destroy', $event))
            ->assertRedirect(route('admin.events.index'));
        $this->assertSoftDeleted($event);
    }

    public function test_result_rejects_training_deleted_event_and_deleted_user(): void
    {
        $moderator = $this->moderator();
        $definition = $this->definition('AUDIT-RESULT');
        $competitionEvent = $this->event($moderator, EventType::Competition, 'wynik-zawody');
        $competition = $this->eventCompetition($competitionEvent, $definition);
        $training = $this->event($moderator, EventType::Training, 'wynik-trening');
        $trainingCompetition = $this->eventCompetition($training, $definition);

        $this->actingAs($moderator)
            ->post(route('admin.results.store'), [
                'event_competition_id' => $trainingCompetition->id,
                'participant_name' => 'Jan Treningowy',
                'score' => '100',
                'status' => ResultStatus::Official->value,
            ])
            ->assertSessionHasErrors('event_competition_id');

        $competitionEvent->delete();

        $this->post(route('admin.results.store'), [
            'event_competition_id' => $competition->id,
            'participant_name' => 'Anna Usunięte Zawody',
            'score' => '99',
            'status' => ResultStatus::Official->value,
        ])->assertSessionHasErrors('event_competition_id');

        $result = EventResult::query()->create([
            'event_competition_id' => $competition->id,
            'participant_name' => 'Historyczny Zawodnik',
            'score' => '88',
            'status' => ResultStatus::Official,
            'entered_by' => $moderator->id,
        ]);

        $this->get(route('admin.results.edit', $result))
            ->assertOk()
            ->assertSeeText('Historyczny Zawodnik');
        $this->put(route('admin.results.update', $result), [
            'event_competition_id' => $competition->id,
            'participant_name' => 'Historyczny Zawodnik',
            'score' => '89',
            'status' => ResultStatus::Official->value,
        ])->assertSessionHasNoErrors();
        self::assertSame('89', $result->fresh()->score);

        $activeEvent = $this->event($moderator, EventType::Competition, 'wynik-aktywny');
        $activeCompetition = $this->eventCompetition($activeEvent, $definition);
        $deletedUser = User::factory()->create();
        $deletedUser->delete();

        $this->post(route('admin.results.store'), [
            'event_competition_id' => $activeCompetition->id,
            'user_id' => $deletedUser->id,
            'participant_name' => 'Usunięty użytkownik',
            'score' => '77',
            'status' => ResultStatus::Official->value,
        ])->assertSessionHasErrors('user_id');

        $historicalUserResult = EventResult::query()->create([
            'event_competition_id' => $activeCompetition->id,
            'user_id' => $deletedUser->id,
            'participant_name' => 'Usunięty użytkownik',
            'score' => '76',
            'status' => ResultStatus::Official,
            'entered_by' => $moderator->id,
        ]);

        $this->get(route('admin.results.edit', $historicalUserResult))
            ->assertOk()
            ->assertSeeText('konto usunięte');
        $this->put(route('admin.results.update', $historicalUserResult), [
            'event_competition_id' => $activeCompetition->id,
            'user_id' => $deletedUser->id,
            'participant_name' => 'Usunięty użytkownik',
            'score' => '75',
            'status' => ResultStatus::Official->value,
        ])->assertSessionHasNoErrors();

        self::assertSame(
            $deletedUser->id,
            $historicalUserResult->fresh()->user_id,
        );
    }

    public function test_user_crud_normalizes_email_and_preserves_password_on_empty_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
        $this->get(route('admin.users.create'))->assertOk();

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Audytowany Użytkownik',
            'email' => '  AUDYT.USER@EXAMPLE.COM ',
            'password' => 'BezpieczneHaslo123',
            'password_confirmation' => 'BezpieczneHaslo123',
            'role' => UserRole::User->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $user = User::query()
            ->where('email', 'audyt.user@example.com')
            ->firstOrFail();
        $passwordHash = $user->password;

        $response->assertRedirect(route('admin.users.edit', $user));
        self::assertTrue(Hash::check('BezpieczneHaslo123', $passwordHash));
        $this->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSeeText($user->name);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Zmieniony Użytkownik',
            'email' => 'AUDYT.USER@EXAMPLE.COM',
            'password' => '',
            'role' => UserRole::Moderator->value,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        self::assertSame('Zmieniony Użytkownik', $user->name);
        self::assertSame(UserRole::Moderator, $user->role);
        self::assertSame($passwordHash, $user->password);

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted($user);
    }

    public function test_club_position_crud_syncs_users_and_rejects_deleted_user(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.positions.index'))
            ->assertOk();
        $this->get(route('admin.positions.create'))->assertOk();

        $response = $this->post(route('admin.positions.store'), [
            'name' => 'Audytowana funkcja',
            'slug' => 'audytowana-funkcja',
            'sort_order' => 10,
            'is_active' => '1',
            'user_ids' => [$member->id],
            'user_sort_orders' => [$member->id => 3],
        ])->assertSessionHasNoErrors();

        $position = ClubPosition::query()->firstOrFail();

        $response->assertRedirect(route('admin.positions.edit', $position));
        $this->assertDatabaseHas('club_position_user', [
            'club_position_id' => $position->id,
            'user_id' => $member->id,
            'sort_order' => 3,
        ]);
        $this->get(route('admin.positions.edit', $position))
            ->assertOk()
            ->assertSeeText($member->name);

        $deletedMember = User::factory()->create();
        $deletedMember->delete();

        $this->put(route('admin.positions.update', $position), [
            'name' => 'Niedozwolona funkcja',
            'slug' => $position->slug,
            'sort_order' => 20,
            'is_active' => '1',
            'user_ids' => [$deletedMember->id],
        ])->assertSessionHasErrors('user_ids.0');

        $this->put(route('admin.positions.update', $position), [
            'name' => 'Zmieniona funkcja',
            'slug' => $position->slug,
            'sort_order' => 20,
            'is_active' => '1',
            'user_ids' => [$member->id],
            'user_sort_orders' => [$member->id => 1],
        ])->assertSessionHasNoErrors();

        self::assertSame('Zmieniona funkcja', $position->fresh()->name);
        $this->assertDatabaseHas('club_position_user', [
            'club_position_id' => $position->id,
            'user_id' => $member->id,
            'sort_order' => 1,
        ]);

        $this->delete(route('admin.positions.destroy', $position))
            ->assertRedirect(route('admin.positions.index'));
        $this->assertDatabaseMissing('club_positions', ['id' => $position->id]);
        $this->assertDatabaseMissing('club_position_user', [
            'club_position_id' => $position->id,
        ]);
    }

    public function test_competition_crud_normalizes_code_and_blocks_used_definition(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.competitions.index'))
            ->assertOk();
        $this->get(route('admin.competitions.create'))->assertOk();

        $response = $this->post(route('admin.competitions.store'), [
            'code' => ' audit-code ',
            'name' => 'Audytowana konkurencja',
            'discipline' => Discipline::Pistol->value,
            'competition_system' => CompetitionSystem::IPSC->value,
            'sort_order' => 10,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $definition = CompetitionDefinition::query()->firstOrFail();

        self::assertSame('AUDIT-CODE', $definition->code);
        $response->assertRedirect(route('admin.competitions.edit', $definition));
        $this->get(route('admin.competitions.edit', $definition))
            ->assertOk()
            ->assertSeeText($definition->name);

        $this->post(route('admin.competitions.store'), [
            'code' => 'audit-code',
            'name' => 'Duplikat',
            'discipline' => Discipline::Rifle->value,
            'competition_system' => CompetitionSystem::ISSF->value,
            'sort_order' => 20,
            'is_active' => '1',
        ])->assertSessionHasErrors('code');

        $this->put(route('admin.competitions.update', $definition), [
            'code' => ' new-audit-code ',
            'name' => 'Zmieniona konkurencja',
            'discipline' => Discipline::Rifle->value,
            'competition_system' => CompetitionSystem::ISSF->value,
            'sort_order' => 20,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $definition->refresh();
        self::assertSame('NEW-AUDIT-CODE', $definition->code);

        $event = $this->event($admin, EventType::Competition, 'uzyta-konkurencja');
        $event->competitions()->attach($definition->id);

        $this->delete(route('admin.competitions.destroy', $definition))
            ->assertSessionHasErrors('competition');
        self::assertTrue($definition->fresh()->exists);

        $event->competitions()->detach($definition->id);

        $this->delete(route('admin.competitions.destroy', $definition))
            ->assertRedirect(route('admin.competitions.index'));
        $this->assertDatabaseMissing('competition_definitions', [
            'id' => $definition->id,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }

    private function moderator(): User
    {
        return User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);
    }

    private function definition(
        string $code,
        bool $isActive = true,
    ): CompetitionDefinition {
        return CompetitionDefinition::query()->create([
            'code' => $code,
            'name' => "Konkurencja {$code}",
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'is_active' => $isActive,
            'sort_order' => 10,
        ]);
    }

    private function event(
        User $creator,
        EventType $type,
        string $slug,
    ): SportEvent {
        return SportEvent::query()->create([
            'title' => "Wydarzenie {$slug}",
            'slug' => $slug,
            'event_type' => $type,
            'start_at' => now()->addDay(),
            'location_name' => 'Strzelnica',
            'status' => PublicationStatus::Draft,
            'is_public' => true,
            'created_by' => $creator->id,
        ]);
    }

    private function eventCompetition(
        SportEvent $event,
        CompetitionDefinition $definition,
    ): EventCompetition {
        return $event->eventCompetitions()->create([
            'competition_definition_id' => $definition->id,
        ]);
    }
}
