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
use Illuminate\Support\Facades\Gate;
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
            ->assertRedirect(route('admin.my-listings.index'))
            ->assertSessionHas('success', 'Szkic ogłoszenia został zapisany.');

        $listing = SaleListing::query()->firstOrFail();
        self::assertSame(SaleListingStatus::Draft, $listing->status);
        self::assertNull($listing->submitted_at);
        self::assertSame($user->id, $listing->user_id);
        self::assertTrue($listing->images()->firstOrFail()->is_primary);
        Storage::disk('public')->assertExists($listing->images()->firstOrFail()->path);
        $this->actingAs($user)
            ->get(route('admin.my-listings.edit', $listing))
            ->assertOk()
            ->assertSee('listing-images', false);
    }

    public function test_pending_intent_creates_and_submits_listing_with_history(): void
    {
        Storage::fake('public');
        Notification::fake();
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.my-listings.store'), [
                ...$this->validData(),
                'intent' => 'pending',
                'images' => [UploadedFile::fake()->image('do-moderacji.jpg', 900, 700)],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.my-listings.index'))
            ->assertSessionHas('success', 'Ogłoszenie zostało wysłane do moderacji.');

        $listing = SaleListing::query()->firstOrFail();
        self::assertSame(SaleListingStatus::Pending, $listing->status);
        self::assertNotNull($listing->submitted_at);
        $this->assertDatabaseHas('sale_listing_moderations', [
            'sale_listing_id' => $listing->id,
            'actor_id' => $user->id,
            'action' => 'submitted',
            'from_status' => SaleListingStatus::Draft->value,
            'to_status' => SaleListingStatus::Pending->value,
        ]);
        Notification::assertSentTo($admin, SaleListingSubmittedNotification::class);
    }

    public function test_jpg_png_and_webp_listing_images_are_accepted_and_stored(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $files = [
            UploadedFile::fake()->image('zdjecie.jpg', 640, 480),
            UploadedFile::fake()->image('zdjecie.png', 640, 480),
            $this->webpUpload('zdjecie.webp'),
        ];

        foreach ($files as $index => $file) {
            $this->actingAs($user)
                ->post(route('admin.my-listings.store'), [
                    ...$this->validData(),
                    'title' => 'Ogłoszenie format '.($index + 1),
                    'intent' => 'draft',
                    'images' => [$file],
                ])
                ->assertSessionHasNoErrors();
        }

        $webpPath = $files[2]->getPathname();
        if (is_file($webpPath)) {
            unlink($webpPath);
        }

        self::assertDatabaseCount('sale_listing_images', 3);
        foreach (SaleListingImage::query()->get() as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_listing_accepts_multiple_images_up_to_the_maximum_of_ten(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $images = array_map(
            static fn (int $number): UploadedFile => UploadedFile::fake()->image("zdjecie-{$number}.jpg", 320, 240),
            range(1, 10),
        );

        $this->actingAs($user)
            ->post(route('admin.my-listings.store'), [
                ...$this->validData(),
                'intent' => 'draft',
                'images' => $images,
            ])
            ->assertSessionHasNoErrors();

        $listing = SaleListing::query()->firstOrFail();
        self::assertSame(10, $listing->images()->count());
        self::assertSame(1, $listing->images()->where('is_primary', true)->count());
    }

    public function test_listing_rejects_an_image_larger_than_six_megabytes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.my-listings.store'), [
                ...$this->validData(),
                'intent' => 'draft',
                'images' => [UploadedFile::fake()->image('za-duze.jpg')->size(6145)],
            ])
            ->assertSessionHasErrors([
                'images.0' => 'Jedno zdjęcie może mieć maksymalnie 6 MB.',
            ]);

        self::assertDatabaseCount('sale_listing_images', 0);
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
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.my-listings.index'))
            ->assertSessionHas('success', 'Zmiany zapisano, a ogłoszenie trafiło do moderacji.');

        $listing->refresh();
        self::assertSame(SaleListingStatus::Pending, $listing->status);
        self::assertNotNull($listing->submitted_at);
        self::assertNull($listing->published_at);
        self::assertNull($listing->approved_by);
        $this->assertDatabaseHas('sale_listing_moderations', [
            'sale_listing_id' => $listing->id,
            'action' => 'submitted',
            'from_status' => SaleListingStatus::Draft->value,
            'to_status' => SaleListingStatus::Pending->value,
        ]);
    }

    public function test_rejected_listing_can_be_edited_and_resubmitted(): void
    {
        Notification::fake();
        $this->admin();
        $user = User::factory()->create();
        $listing = SaleListing::factory()->for($user, 'author')->create([
            'status' => SaleListingStatus::Rejected,
            'rejection_reason' => 'Uzupełnij opis stanu przedmiotu.',
            'rejected_at' => now()->subHour(),
        ]);
        SaleListingImage::factory()->for($listing, 'listing')->create();

        $this->actingAs($user)
            ->put(route('admin.my-listings.update', $listing), [
                ...$this->validData(),
                'title' => 'Poprawione ogłoszenie po odrzuceniu',
                'intent' => 'pending',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.my-listings.index'));

        $listing->refresh();
        self::assertSame(SaleListingStatus::Pending, $listing->status);
        self::assertNotNull($listing->submitted_at);
        self::assertNull($listing->rejection_reason);
        self::assertNull($listing->rejected_at);
        $this->assertDatabaseHas('sale_listing_moderations', [
            'sale_listing_id' => $listing->id,
            'action' => 'submitted',
            'from_status' => SaleListingStatus::Rejected->value,
            'to_status' => SaleListingStatus::Pending->value,
        ]);
    }

    public function test_owner_permissions_follow_the_listing_status_workflow(): void
    {
        $owner = User::factory()->create();
        $allowedUpdates = [SaleListingStatus::Draft, SaleListingStatus::Rejected, SaleListingStatus::Approved];
        $allowedSubmissions = [SaleListingStatus::Draft, SaleListingStatus::Rejected];

        foreach (SaleListingStatus::cases() as $status) {
            $listing = SaleListing::factory()->for($owner, 'author')->create(['status' => $status]);
            $gate = Gate::forUser($owner);

            self::assertSame(in_array($status, $allowedUpdates, true), $gate->allows('update', $listing), $status->value);
            self::assertSame(in_array($status, $allowedSubmissions, true), $gate->allows('submit', $listing), $status->value);
            self::assertSame($status !== SaleListingStatus::Pending, $gate->allows('delete', $listing), $status->value);
            self::assertSame($status === SaleListingStatus::Approved, $gate->allows('markAsSold', $listing), $status->value);
            self::assertTrue($gate->allows('view', $listing), $status->value);
        }
    }

    public function test_forbidden_owner_actions_are_blocked_by_endpoints(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $pending = SaleListing::factory()->for($owner, 'author')->pending()->create();
        $draft = SaleListing::factory()->for($owner, 'author')->create();
        $foreign = SaleListing::factory()->for($other, 'author')->create();

        $this->actingAs($owner)->get(route('admin.my-listings.edit', $pending))->assertForbidden();
        $this->actingAs($owner)->delete(route('admin.my-listings.destroy', $pending))->assertForbidden();
        $this->actingAs($owner)->post(route('admin.my-listings.submit', $pending))->assertForbidden();
        $this->actingAs($owner)->post(route('admin.my-listings.sold', $draft))->assertForbidden();
        $this->actingAs($owner)->post(route('admin.my-listings.duplicate', $foreign))->assertForbidden();

        self::assertSame(SaleListingStatus::Pending, $pending->fresh()->status);
        self::assertFalse($pending->fresh()->trashed());
        self::assertSame(SaleListingStatus::Draft, $draft->fresh()->status);
    }

    public function test_expiration_command_is_idempotent_and_does_not_delete_listing(): void
    {
        $expiresAt = now()->subMinute();
        $listing = SaleListing::factory()->approved()->create(['expires_at' => $expiresAt]);

        $this->artisan('listings:expire')->assertSuccessful();
        $this->artisan('listings:expire')->assertSuccessful();

        self::assertSame(SaleListingStatus::Expired, $listing->fresh()->status);
        self::assertSame($expiresAt->toDateTimeString(), $listing->fresh()->expires_at->toDateTimeString());
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
        $sentAt = $listing->fresh()->expiration_reminder_sent_at;
        $this->artisan('listings:expire')->assertSuccessful();

        self::assertNotNull($sentAt);
        self::assertTrue($listing->fresh()->expiration_reminder_sent_at->equalTo($sentAt));
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

    public function test_listing_detail_keeps_long_public_contact_values_visible(): void
    {
        $phone = '+48'.str_repeat('1', 29);
        $email = str_repeat('a', 64).'@'.str_repeat('b', 63).'.'.str_repeat('c', 63).'.example';
        $listing = SaleListing::factory()->approved()->create([
            'contact_phone' => $phone,
            'contact_email' => $email,
            'show_phone' => true,
            'show_email' => true,
        ]);

        $this->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSee($phone)
            ->assertSee($email)
            ->assertSeeText('Bezpieczna transakcja');

        $styles = (string) file_get_contents(resource_path('css/pages/listings.css'));
        self::assertMatchesRegularExpression('/\.listing-contact-card\s*\{[^}]*min-width:\s*0;[^}]*max-width:\s*100%;/s', $styles);
        self::assertDoesNotMatchRegularExpression('/\.listing-contact-card\s*\{[^}]*overflow:\s*hidden;/s', $styles);
        self::assertMatchesRegularExpression('/\.listing-contact-actions__value\s*\{[^}]*overflow-wrap:\s*anywhere;/s', $styles);
        self::assertMatchesRegularExpression('/\.listing-disclaimer p\s*\{[^}]*overflow-wrap:\s*anywhere;/s', $styles);
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
        Storage::fake((string) config('media.disk'));
        $user = User::factory()->create();
        $listing = SaleListing::factory()->for($user, 'author')->approved()->create();
        $firstImage = SaleListingImage::factory()->for($listing, 'listing')->create();
        $secondImage = SaleListingImage::factory()->for($listing, 'listing')->create([
            'is_primary' => false,
            'sort_order' => 1,
        ]);
        Storage::disk((string) config('media.disk'))->put($firstImage->path, 'pierwsze zdjęcie');
        Storage::disk((string) config('media.disk'))->put($secondImage->path, 'drugie zdjęcie');

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
            ->assertSee('class="listing-detail__panel listing-report-section panel-card"', false)
            ->assertSeeText('Bezpieczna transakcja')
            ->assertSee('aria-current="true"', false);

        $detail = (string) $this->get(route('listings.show', $listing))->getContent();
        self::assertLessThan(
            strpos($detail, '<aside class="listing-contact-card'),
            strpos($detail, 'class="listing-detail__panel listing-report-section panel-card"'),
        );
    }

    public function test_my_listings_show_only_direct_actions_allowed_for_each_status(): void
    {
        $user = User::factory()->create();
        $draft = SaleListing::factory()->for($user, 'author')->create(['title' => 'Szkic akcji']);
        $pending = SaleListing::factory()->for($user, 'author')->pending()->create(['title' => 'Oczekujące akcje']);
        $approved = SaleListing::factory()->for($user, 'author')->approved()->create(['title' => 'Zatwierdzone akcje']);

        $draftResponse = $this->actingAs($user)->get(route('admin.my-listings.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee('href="'.route('admin.my-listings.edit', $draft).'"', false)
            ->assertSee('action="'.route('admin.my-listings.duplicate', $draft).'"', false)
            ->assertSee('action="'.route('admin.my-listings.destroy', $draft).'"', false)
            ->assertDontSee('Więcej działań')
            ->assertDontSee('Wyślij do moderacji');
        self::assertStringNotContainsString('target="_blank"', $draftResponse->getContent());

        $this->actingAs($user)->get(route('admin.my-listings.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('action="'.route('admin.my-listings.duplicate', $pending).'"', false)
            ->assertDontSee('href="'.route('admin.my-listings.edit', $pending).'"', false)
            ->assertDontSee('action="'.route('admin.my-listings.destroy', $pending).'"', false)
            ->assertDontSee('Oznacz jako sprzedane')
            ->assertDontSee('>Podgląd<', false);

        $this->actingAs($user)->get(route('admin.my-listings.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('href="'.route('admin.my-listings.edit', $approved).'"', false)
            ->assertSee('action="'.route('admin.my-listings.sold', $approved).'"', false)
            ->assertSee('href="'.route('listings.show', $approved).'"', false)
            ->assertSee('action="'.route('admin.my-listings.duplicate', $approved).'"', false)
            ->assertSee('action="'.route('admin.my-listings.destroy', $approved).'"', false)
            ->assertSee('>Edytuj<', false)
            ->assertSee('>Oznacz jako sprzedane<', false)
            ->assertSee('>Podgląd<', false)
            ->assertSee('>Kopiuj<', false)
            ->assertSee('>Usuń<', false);
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
        $this->post(route('listings.report', $listing), $payload)
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Dziękujemy. Zgłoszenie zostało przekazane administratorowi.');
        self::assertDatabaseCount('sale_listing_reports', 1);
    }

    public function test_form_loading_state_preserves_the_clicked_submitter_value(): void
    {
        $script = (string) file_get_contents(resource_path('js/modules/form-state.js'));

        self::assertStringContainsString('event.submitter', $script);
        self::assertStringContainsString('submitterField.name = submitter.name', $script);
        self::assertStringContainsString('submitterField.value = submitter.value', $script);
        self::assertStringContainsString('state.submitterField?.remove()', $script);
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

    private function webpUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ks-krokus-webp-');

        if ($path === false) {
            self::fail('Nie udało się utworzyć pliku testowego WebP.');
        }

        $image = imagecreatetruecolor(32, 32);
        self::assertNotFalse($image);
        self::assertTrue(imagewebp($image, $path));
        imagedestroy($image);

        return new UploadedFile($path, $name, 'image/webp', null, true);
    }
}
