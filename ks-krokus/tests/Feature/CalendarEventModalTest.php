<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompetitionSystem;
use App\Enums\Discipline;
use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Models\CompetitionDefinition;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CalendarEventModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_event_view_still_uses_public_details(): void
    {
        $event = $this->createEvent([
            'description' => 'Opis pełnego widoku wydarzenia.',
            'address' => 'ul. Sportowa 10, Nowy Sącz',
        ]);

        $this->get(route('calendar.show', $event))
            ->assertOk()
            ->assertSeeText($event->title)
            ->assertSeeText('Opis pełnego widoku wydarzenia.')
            ->assertSeeText('ul. Sportowa 10, Nowy Sącz')
            ->assertSeeText('Przypomnienie e-mail');
    }

    public function test_modal_endpoint_returns_public_event_html_with_basic_details(): void
    {
        $creator = User::factory()->create([
            'name' => 'Dane administracyjne',
            'email' => 'prywatny-admin@example.test',
        ]);
        $competition = CompetitionDefinition::query()->create([
            'code' => 'MODAL-IPSC',
            'name' => 'Pistolet dynamiczny',
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'is_active' => true,
        ]);
        $event = $this->createEvent([
            'title' => 'Otwarte zawody modalowe',
            'slug' => 'otwarte-zawody-modalowe',
            'description' => 'Publiczny opis zawodów.',
            'start_at' => '2026-09-12 09:30:00',
            'end_at' => '2026-09-12 16:45:00',
            'address' => 'ul. Tarcza 7, Nowy Sącz',
            'discipline' => Discipline::Pistol,
            'competition_system' => CompetitionSystem::IPSC,
            'registration_url' => 'https://zapisy.example.test/zawody',
            'created_by' => $creator->id,
        ]);
        $event->competitions()->attach($competition);

        $this->get(route('calendar.modal', $event))
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('data-event-details', false)
            ->assertSee('id="event-dialog-title"', false)
            ->assertSee('id="event-dialog-description"', false)
            ->assertSeeText('Otwarte zawody modalowe')
            ->assertSeeText('Zawody')
            ->assertSeeText('12.09.2026 09:30')
            ->assertSeeText('12.09.2026 16:45')
            ->assertSeeText('Strzelnica KS Krokus')
            ->assertSeeText('ul. Tarcza 7, Nowy Sącz')
            ->assertSeeText('Publiczny opis zawodów.')
            ->assertSeeText('Pistolet')
            ->assertSeeText('IPSC')
            ->assertSeeText('Pistolet dynamiczny')
            ->assertSee('https://zapisy.example.test/zawody', false)
            ->assertSeeText('Zaloguj się')
            ->assertDontSeeText('Dane administracyjne')
            ->assertDontSee('prywatny-admin@example.test', false)
            ->assertDontSee('created_by', false);
    }

    public function test_modal_endpoint_does_not_return_draft_event(): void
    {
        $event = $this->createEvent([
            'slug' => 'szkic-modala',
            'status' => PublicationStatus::Draft,
        ]);

        $this->get(route('calendar.modal', $event))->assertNotFound();
    }

    public function test_modal_endpoint_does_not_return_non_public_event(): void
    {
        $event = $this->createEvent([
            'slug' => 'prywatne-wydarzenie-modala',
            'is_public' => false,
        ]);

        $this->get(route('calendar.modal', $event))->assertNotFound();
    }

    public function test_modal_endpoint_returns_not_found_for_missing_event(): void
    {
        $this->get(route('calendar.modal', ['slug' => 'brakujace-wydarzenie']))
            ->assertNotFound();
    }

    public function test_calendar_event_keeps_full_view_fallback_link(): void
    {
        $event = $this->createEvent([
            'start_at' => '2026-09-12 09:30:00',
        ]);

        $this->get(route('calendar.index', ['month' => 9, 'year' => 2026]))
            ->assertOk()
            ->assertSee('href="'.route('calendar.show', $event).'"', false)
            ->assertSee('data-event-dialog-trigger', false)
            ->assertSee('data-event-dialog-url="'.route('calendar.modal', $event).'"', false)
            ->assertSee('aria-labelledby="event-dialog-title"', false)
            ->assertSee('aria-describedby="event-dialog-description"', false);
    }

    public function test_existing_calendar_filter_still_limits_events_with_modal_links(): void
    {
        $training = $this->createEvent([
            'title' => 'Filtrowany trening',
            'slug' => 'filtrowany-trening',
            'event_type' => EventType::Training,
            'start_at' => '2026-09-12 09:30:00',
        ]);
        $competition = $this->createEvent([
            'title' => 'Ukryte przez filtr zawody',
            'slug' => 'ukryte-przez-filtr-zawody',
            'event_type' => EventType::Competition,
            'start_at' => '2026-09-13 09:30:00',
        ]);

        $this->get(route('calendar.index', [
            'month' => 9,
            'year' => 2026,
            'event_type' => EventType::Training->value,
        ]))
            ->assertOk()
            ->assertSeeText($training->title)
            ->assertSee(route('calendar.modal', $training), false)
            ->assertDontSeeText($competition->title)
            ->assertDontSee(route('calendar.modal', $competition), false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEvent(array $attributes = []): SportEvent
    {
        return SportEvent::query()->create(array_merge([
            'title' => 'Publiczne wydarzenie testowe',
            'slug' => 'publiczne-wydarzenie-testowe',
            'event_type' => EventType::Competition,
            'description' => 'Opis wydarzenia testowego.',
            'start_at' => '2026-09-12 09:30:00',
            'end_at' => '2026-09-12 16:45:00',
            'location_name' => 'Strzelnica KS Krokus',
            'status' => PublicationStatus::Published,
            'is_public' => true,
            'email_reminders_enabled' => true,
        ], $attributes));
    }
}
