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
}
