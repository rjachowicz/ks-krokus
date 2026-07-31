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
use App\Models\EventResult;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => UserRole::Moderator->value,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');

        self::assertSame(
            UserRole::Admin,
            $admin->fresh()->role,
        );
    }

    public function test_competition_with_results_cannot_be_removed_from_event(): void
    {
        $moderator = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_active' => true,
        ]);

        $definition = CompetitionDefinition::query()->create([
            'code' => 'IPSC-HG-TEST',
            'name' => 'IPSC Handgun Test',
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $event = SportEvent::query()->create([
            'title' => 'Zawody testowe',
            'slug' => 'zawody-testowe',
            'event_type' => EventType::Competition,
            'start_at' => now()->addDay(),
            'location_name' => 'Strzelnica',
            'status' => PublicationStatus::Published,
            'is_public' => true,
            'created_by' => $moderator->id,
        ]);

        $eventCompetition = $event->eventCompetitions()->create([
            'competition_definition_id' => $definition->id,
        ]);

        EventResult::query()->create([
            'event_competition_id' => $eventCompetition->id,
            'participant_name' => 'Jan Testowy',
            'score' => '100',
            'status' => ResultStatus::Official,
            'entered_by' => $moderator->id,
        ]);

        $this->actingAs($moderator)
            ->put(route('admin.events.update', $event), [
                'title' => $event->title,
                'event_type' => EventType::Competition->value,
                'start_at' => $event->start_at->format('Y-m-d H:i:s'),
                'location_name' => $event->location_name,
                'status' => PublicationStatus::Published->value,
                'is_public' => '1',
                'competition_ids' => [],
            ])
            ->assertSessionHasErrors('competition_ids');

        self::assertTrue(
            $event->eventCompetitions()->whereKey($eventCompetition->id)->exists(),
        );
        self::assertTrue(
            EventResult::query()->whereKey($eventCompetition->results()->firstOrFail()->id)->exists(),
        );
    }

    public function test_user_form_returns_natural_polish_password_errors(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'niepoprawny-adres',
                'password' => 'same-male-litery',
                'password_confirmation' => 'same-male-litery',
                'role' => UserRole::User->value,
            ])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        foreach (session('errors')->all() as $message) {
            self::assertStringNotContainsString('validation.', $message);
            self::assertStringNotContainsString('The ', $message);
        }
    }
}
