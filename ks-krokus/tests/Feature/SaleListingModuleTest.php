<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SaleListingCategory;
use App\Enums\SaleListingFirearmType;
use App\Enums\SaleListingReportReason;
use App\Enums\SaleListingStatus;
use App\Enums\UserRole;
use App\Models\SaleListing;
use App\Models\SaleListingImage;
use App\Models\User;
use App\Notifications\SaleListingApprovedNotification;
use App\Notifications\SaleListingExpiringNotification;
use App\Notifications\SaleListingRejectedNotification;
use App\Notifications\SaleListingSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SaleListingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_a_listing(): void
    {
        $this->get(route('admin.my-listings.create'))->assertRedirect(route('login'));
        $this->post(route('admin.my-listings.store'), $this->validData())->assertRedirect(route('login'));
        self::assertDatabaseCount('sale_listings', 0);
    }

    public function test_user_can_create_a_draft_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.my-listings.store'), [
                ...$this->validData(),
                'intent' => 'draft',
                'images' => [UploadedFile::fake()->image('pistolet.jpg', 900, 700)],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.my-listings.index'));

        $listing = SaleListing::query()->firstOrFail();
        self::assertSame(SaleListingStatus::Draft, $listing->status);
        self::assertSame($user->id, $listing->user_id);
        self::assertTrue($listing->images()->firstOrFail()->is_primary);
        Storage::disk('public')->assertExists($listing->images()->firstOrFail()->path);
        $this->actingAs($user)
            ->get(route('admin.my-listings.edit', $listing))
            ->assertOk()
            ->assertSee('listing-images', false);
    }

    public function test_user_can_submit_a_draft_and_admin_is_notified(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $user = User::factory()->create();
        $listing = SaleListing::factory()->for($user, 'author')->create();
        SaleListingImage::factory()->for($listing, 'listing')->create();

        $this->actingAs($user)
            ->post(route('admin.my-listings.submit', $listing))
            ->assertSessionHasNoErrors();

        self::assertSame(SaleListingStatus::Pending, $listing->fresh()->status);
        self::assertNotNull($listing->fresh()->submitted_at);
        Notification::assertSentTo($admin, SaleListingSubmittedNotification::class);
    }

    public function test_user_cannot_approve_own_or_edit_someone_elses_listing(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $own = SaleListing::factory()->for($user, 'author')->pending()->create();
        $foreign = SaleListing::factory()->for($other, 'author')->create();

        $this->actingAs($user)
            ->post(route('admin.sale-listings.approve', $own))
            ->assertForbidden();
        $this->actingAs($user)
            ->get(route('admin.my-listings.edit', $foreign))
            ->assertForbidden();

        self::assertSame(SaleListingStatus::Pending, $own->fresh()->status);
    }

    public function test_admin_can_approve_a_pending_listing(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $author = User::factory()->create();
        $listing = SaleListing::factory()->for($author, 'author')->pending()->create();
        SaleListingImage::factory()->for($listing, 'listing')->create();

        $this->actingAs($admin)
            ->get(route('admin.sale-listings.edit', $listing))
            ->assertOk()
            ->assertSee('Decyzja moderacyjna');

        $this->actingAs($admin)
            ->post(route('admin.sale-listings.approve', $listing))
            ->assertSessionHasNoErrors();

        $listing->refresh();
        self::assertSame(SaleListingStatus::Approved, $listing->status);
        self::assertSame($admin->id, $listing->approved_by);
        self::assertNotNull($listing->published_at);
        self::assertEquals(365, $listing->published_at->diffInDays($listing->expires_at));
        Notification::assertSentTo($author, SaleListingApprovedNotification::class);
    }

    public function test_admin_can_reject_only_with_a_reason(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $author = User::factory()->create();
        $listing = SaleListing::factory()->for($author, 'author')->pending()->create();

        $this->actingAs($admin)
            ->post(route('admin.sale-listings.reject', $listing), [])
            ->assertSessionHasErrors('rejection_reason');

        $reason = 'Opis wymaga uzupełnienia informacji o stanie przedmiotu.';
        $this->actingAs($admin)
            ->post(route('admin.sale-listings.reject', $listing), ['rejection_reason' => $reason])
            ->assertSessionHasNoErrors();

        $listing->refresh();
        self::assertSame(SaleListingStatus::Rejected, $listing->status);
        self::assertSame($reason, $listing->rejection_reason);
        self::assertSame($admin->id, $listing->rejected_by);
        Notification::assertSentTo($author, SaleListingRejectedNotification::class);
    }

    public function test_only_approved_current_listings_are_public(): void
    {
        $approved = SaleListing::factory()->approved()->create(['title' => 'Oferta publiczna']);
        $pending = SaleListing::factory()->pending()->create(['title' => 'Oferta oczekująca']);
        $rejected = SaleListing::factory()->create([
            'title' => 'Oferta odrzucona',
            'status' => SaleListingStatus::Rejected,
        ]);
        SaleListingImage::factory()->for($approved, 'listing')->create();

        $this->get(route('listings.index'))
            ->assertOk()
            ->assertSee('Oferta publiczna')
            ->assertDontSee('Oferta oczekująca')
            ->assertDontSee('Oferta odrzucona');
        $this->get(route('listings.show', $approved))->assertOk();
        $this->get(route('listings.show', $pending))->assertNotFound();
        $this->get(route('listings.show', $rejected))->assertNotFound();
    }

    public function test_owner_can_mark_approved_listing_as_sold(): void
    {
        $user = User::factory()->create();
        $listing = SaleListing::factory()->for($user, 'author')->approved()->create();

        $this->actingAs($user)
            ->post(route('admin.my-listings.sold', $listing))
            ->assertSessionHasNoErrors();

        self::assertSame(SaleListingStatus::Sold, $listing->fresh()->status);
        self::assertNotNull($listing->fresh()->sold_at);
        $this->get(route('listings.show', $listing))->assertNotFound();
    }

    public function test_approved_listing_edited_by_owner_returns_to_moderation(): void
    {
        Notification::fake();
        $this->admin();
        $user = User::factory()->create();
        $listing = SaleListing::factory()->for($user, 'author')->approved()->create();
        SaleListingImage::factory()->for($listing, 'listing')->create();

        $this->actingAs($user)
            ->put(route('admin.my-listings.update', $listing), [
                ...$this->validData(),
                'title' => 'Istotnie zmieniona oferta',
                'intent' => 'pending',
            ])
            ->assertSessionHasNoErrors();

        $listing->refresh();
        self::assertSame(SaleListingStatus::Pending, $listing->status);
        self::assertNull($listing->published_at);
        self::assertNull($listing->approved_by);
    }

    public function test_expiration_command_is_idempotent_and_does_not_delete_listing(): void
    {
        $listing = SaleListing::factory()->approved()->create(['expires_at' => now()->subMinute()]);

        $this->artisan('listings:expire')->assertSuccessful();
        $this->artisan('listings:expire')->assertSuccessful();

        self::assertSame(SaleListingStatus::Expired, $listing->fresh()->status);
        self::assertFalse($listing->fresh()->trashed());
        self::assertSame(1, $listing->moderations()->where('action', 'expired')->count());
    }

    public function test_expiration_command_sends_one_reminder_before_deadline(): void
    {
        Notification::fake();
        $author = User::factory()->create();
        $listing = SaleListing::factory()->for($author, 'author')->approved()->create([
            'expires_at' => now()->addDays(5),
        ]);

        $this->artisan('listings:expire')->assertSuccessful();
        $this->artisan('listings:expire')->assertSuccessful();

        self::assertNotNull($listing->fresh()->expiration_reminder_sent_at);
        Notification::assertSentToTimes($author, SaleListingExpiringNotification::class, 1);
    }

    public function test_validation_messages_and_image_rules_are_polish(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.my-listings.store'), [
            'title' => '',
            'category' => '',
            'description' => 'Za krótko',
            'price' => -1,
            'intent' => 'pending',
            'images' => [UploadedFile::fake()->create('plik.pdf', 20, 'application/pdf')],
        ]);

        $response->assertSessionHasErrors(['title', 'category', 'description', 'price', 'images.0']);
        foreach (session('errors')->all() as $message) {
            self::assertStringNotContainsString('validation.', $message);
        }

        $tooMany = array_map(
            static fn (int $number): UploadedFile => UploadedFile::fake()->image("{$number}.jpg"),
            range(1, 11),
        );
        $this->actingAs($user)
            ->post(route('admin.my-listings.store'), [...$this->validData(), 'intent' => 'pending', 'images' => $tooMany])
            ->assertSessionHasErrors('images');
    }

    public function test_uploaded_image_can_be_removed_with_all_derived_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.my-listings.store'), [
            ...$this->validData(),
            'intent' => 'draft',
            'images' => [UploadedFile::fake()->image('oferta.jpg', 900, 700)],
        ]);
        $listing = SaleListing::query()->firstOrFail();
        $image = $listing->images()->firstOrFail();
        $paths = $image->filePaths();

        $this->actingAs($user)->put(route('admin.my-listings.update', $listing), [
            ...$this->validData(),
            'intent' => 'draft',
            'delete_images' => [$image->id],
        ])->assertSessionHasNoErrors();

        self::assertDatabaseMissing('sale_listing_images', ['id' => $image->id]);
        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_phone_and_email_are_only_public_with_explicit_consent(): void
    {
        $listing = SaleListing::factory()->approved()->create([
            'contact_phone' => '+48 600 111 222',
            'contact_email' => 'oferta@example.com',
            'show_phone' => false,
            'show_email' => false,
        ]);
        SaleListingImage::factory()->for($listing, 'listing')->create();

        $this->get(route('listings.show', $listing))
            ->assertDontSee('+48 600 111 222')
            ->assertDontSee('oferta@example.com');

        $listing->update(['show_phone' => true, 'show_email' => true]);
        $this->get(route('listings.show', $listing))
            ->assertSee('+48 600 111 222')
            ->assertSee('oferta@example.com');
    }

    public function test_listing_is_soft_deleted_and_admin_can_restore_it(): void
    {
        $admin = $this->admin();
        $listing = SaleListing::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.sale-listings.destroy', $listing))
            ->assertSessionHasNoErrors();
        self::assertSoftDeleted($listing);

        $this->actingAs($admin)
            ->post(route('admin.sale-listings.restore', $listing))
            ->assertSessionHasNoErrors();
        self::assertNotNull($listing->fresh());
        self::assertFalse($listing->fresh()->trashed());
    }

    public function test_public_filters_limit_results(): void
    {
        $matching = SaleListing::factory()->approved()->create([
            'title' => 'Karabinek Alfa',
            'category' => SaleListingCategory::LongGun,
            'firearm_type' => SaleListingFirearmType::Carbine,
            'caliber' => '.223 Rem',
            'price' => 3500,
        ]);
        SaleListing::factory()->approved()->create([
            'title' => 'Pistolet Beta',
            'category' => SaleListingCategory::Handgun,
            'firearm_type' => SaleListingFirearmType::Pistol,
            'caliber' => '9×19 mm',
            'price' => 5000,
        ]);

        $this->get(route('listings.index', [
            'q' => 'Alfa',
            'category' => SaleListingCategory::LongGun->value,
            'type' => SaleListingFirearmType::Carbine->value,
            'caliber' => '.223 Rem',
            'price_from' => 3000,
            'price_to' => 4000,
            'sort' => 'price_asc',
        ]))->assertOk()->assertSee($matching->title)->assertDontSee('Pistolet Beta');
    }

    public function test_refactored_listing_views_keep_neutral_forms_and_accessible_gallery_controls(): void
    {
        $user = User::factory()->create();
        $listing = SaleListing::factory()->for($user, 'author')->approved()->create();
        SaleListingImage::factory()->for($listing, 'listing')->create();
        SaleListingImage::factory()->for($listing, 'listing')->create([
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $formResponse = $this->actingAs($user)
            ->get(route('admin.my-listings.edit', $listing))
            ->assertOk()
            ->assertSee('class="form-layout listing-form"', false)
            ->assertSee('class="form-section__header"', false)
            ->assertDontSee('admin-form-grid', false)
            ->assertDontSee('admin-form-actions', false);

        self::assertSame(7, preg_match_all('/<section class="form-section(?: |")/', $formResponse->getContent()));

        $this->get(route('listings.index'))
            ->assertOk()
            ->assertSee('class="listing-filters__more"', false)
            ->assertSee('class="listing-results-bar"', false);

        $this->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSee('data-listing-gallery', false)
            ->assertSee('data-listing-gallery-thumbnail', false)
            ->assertSee('data-listing-lightbox', false)
            ->assertSee('aria-current="true"', false);
    }

    public function test_public_report_is_validated_and_deduplicated(): void
    {
        $listing = SaleListing::factory()->approved()->create();
        SaleListingImage::factory()->for($listing, 'listing')->create();

        $this->post(route('listings.report', $listing), [])->assertSessionHasErrors('reason');

        $payload = [
            'reason' => SaleListingReportReason::Outdated->value,
            'details' => 'Przedmiot został już sprzedany.',
        ];
        $this->post(route('listings.report', $listing), $payload)->assertSessionHasNoErrors();
        $this->post(route('listings.report', $listing), $payload)->assertSessionHasNoErrors();
        self::assertDatabaseCount('sale_listing_reports', 1);
    }

    public function test_moderator_can_hide_and_flag_but_cannot_approve(): void
    {
        $moderator = User::factory()->create(['role' => UserRole::Moderator]);
        $listing = SaleListing::factory()->pending()->create();

        $this->actingAs($moderator)
            ->post(route('admin.sale-listings.approve', $listing))
            ->assertForbidden();
        $this->actingAs($moderator)
            ->post(route('admin.sale-listings.hide', $listing))
            ->assertSessionHasNoErrors();
        $this->actingAs($moderator)
            ->post(route('admin.sale-listings.flag', $listing), ['note' => 'Administrator powinien sprawdzić dane kontaktowe.'])
            ->assertSessionHasNoErrors();

        self::assertTrue($listing->fresh()->is_hidden);
        self::assertDatabaseHas('sale_listing_moderations', ['sale_listing_id' => $listing->id, 'action' => 'flagged']);
    }

    /** @return array<string, mixed> */
    private function validData(): array
    {
        return [
            'title' => 'Pistolet sportowy do treningu',
            'category' => SaleListingCategory::Handgun->value,
            'firearm_type' => SaleListingFirearmType::Pistol->value,
            'manufacturer' => 'CZ',
            'model' => 'Shadow 2',
            'caliber' => '9×19 mm',
            'condition' => 'very_good',
            'year_of_manufacture' => 2024,
            'price' => '4500.00',
            'price_negotiable' => '1',
            'description' => 'Pistolet w bardzo dobrym stanie, regularnie czyszczony, z kompletem magazynków.',
            'location' => 'Nowy Sącz',
            'contact_name' => 'Jan',
            'contact_phone' => '+48 600 000 000',
            'contact_email' => 'jan@example.com',
            'show_phone' => '1',
            'show_email' => '1',
        ];
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    }
}
