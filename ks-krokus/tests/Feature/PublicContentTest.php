<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventType;
use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\SportEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_post_is_not_public(): void
    {
        $author = User::factory()->create([
            'role' => UserRole::Moderator,
        ]);

        $post = Post::query()->create([
            'title' => 'Szkic',
            'slug' => 'szkic',
            'content' => 'Treść szkicu',
            'status' => PublicationStatus::Draft,
            'author_id' => $author->id,
        ]);

        $this->get(route('news.show', $post))
            ->assertNotFound();
    }

    public function test_public_event_is_visible_in_calendar(): void
    {
        $event = SportEvent::query()->create([
            'title' => 'Trening pistoletowy',
            'slug' => 'trening-pistoletowy',
            'event_type' => EventType::Training,
            'start_at' => now()->addDay(),
            'location_name' => 'Strzelnica KS Krokus',
            'status' => PublicationStatus::Published,
            'is_public' => true,
        ]);

        $this->get(route('calendar.show', $event))
            ->assertOk()
            ->assertSeeText('Trening pistoletowy');
    }

    public function test_month_calendar_displays_only_events_from_selected_month(): void
    {
        $visibleEvent = SportEvent::query()->create([
            'title' => 'Zawody lipcowe',
            'slug' => 'zawody-lipcowe',
            'event_type' => EventType::Competition,
            'start_at' => '2026-07-18 10:00:00',
            'location_name' => 'Strzelnica',
            'status' => PublicationStatus::Published,
            'is_public' => true,
        ]);

        SportEvent::query()->create([
            'title' => 'Trening sierpniowy',
            'slug' => 'trening-sierpniowy',
            'event_type' => EventType::Training,
            'start_at' => '2026-08-02 09:00:00',
            'location_name' => 'Strzelnica',
            'status' => PublicationStatus::Published,
            'is_public' => true,
        ]);

        $this->get(route('calendar.index', ['month' => 7, 'year' => 2026]))
            ->assertOk()
            ->assertSeeText($visibleEvent->title)
            ->assertDontSeeText('Trening sierpniowy')
            ->assertSeeText('Lipiec 2026');
    }

    public function test_month_calendar_keeps_event_type_filter(): void
    {
        foreach (EventType::cases() as $type) {
            SportEvent::query()->create([
                'title' => $type === EventType::Training ? 'Tylko trening' : 'Tylko zawody',
                'slug' => $type->value,
                'event_type' => $type,
                'start_at' => '2026-07-18 10:00:00',
                'location_name' => 'Strzelnica',
                'status' => PublicationStatus::Published,
                'is_public' => true,
            ]);
        }

        $this->get(route('calendar.index', [
            'month' => 7,
            'year' => 2026,
            'event_type' => EventType::Training->value,
        ]))
            ->assertOk()
            ->assertSeeText('Tylko trening')
            ->assertDontSeeText('Tylko zawody');
    }
}
