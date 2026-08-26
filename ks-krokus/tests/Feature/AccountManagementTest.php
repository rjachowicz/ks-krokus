<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Discipline;
use App\Enums\MemberAgeCategory;
use App\Enums\MemberVerificationStatus;
use App\Enums\UserRole;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_account_section(): void
    {
        $this->get(route('account.show'))->assertRedirect(route('login'));
        $this->patch(route('account.profile.update'), [])->assertRedirect(route('login'));
        $this->patch(route('account.email.update'), [])->assertRedirect(route('login'));
        $this->put(route('account.password.update'), [])->assertRedirect(route('login'));
    }

    public function test_user_sees_only_own_account_and_member_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Jan Kowalski',
            'phone' => '+48 500 100 200',
        ]);
        $other = User::factory()->create(['name' => 'Cudzy Użytkownik']);
        MemberProfile::factory()->create([
            'user_id' => $user,
            'club_member_number' => 'CZ-100',
            'age_category' => MemberAgeCategory::Junior,
            'disciplines' => [Discipline::Pistol->value],
        ]);
        $profile = $user->memberProfile()->firstOrFail();

        self::assertTrue(Gate::forUser($user)->allows('view', $profile));
        self::assertTrue(Gate::forUser($user)->denies('update', $profile));
        self::assertTrue(Gate::forUser($user)->denies('view', MemberProfile::factory()->create([
            'user_id' => $other,
        ])));

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSeeText('Jan Kowalski')
            ->assertSee('value="+48 500 100 200"', false)
            ->assertSeeText('CZ-100')
            ->assertSeeText('Junior')
            ->assertSeeText('Pistolet')
            ->assertDontSeeText('Cudzy Użytkownik');

        $this->actingAs($user)
            ->get('/moje-konto/'.$other->getKey())
            ->assertNotFound();
    }

    public function test_user_updates_allowed_profile_fields_without_changing_role_or_verification_data(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'show_email_publicly' => false,
            'show_phone_publicly' => false,
        ]);
        $profile = MemberProfile::factory()->create([
            'user_id' => $user,
            'club_member_number' => 'CZ-OLD',
        ]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => '  Anna Nowak  ',
                'phone' => '  +48 600 700 800  ',
                'show_email_publicly' => '1',
                'show_phone_publicly' => '1',
                'role' => UserRole::Admin->value,
                'is_active' => false,
                'club_member_number' => 'CZ-HACK',
                'verification_status' => MemberVerificationStatus::Verified->value,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Dane profilu zostały zapisane.');

        $user->refresh();
        self::assertSame('Anna Nowak', $user->name);
        self::assertSame('+48 600 700 800', $user->phone);
        self::assertTrue($user->show_email_publicly);
        self::assertTrue($user->show_phone_publicly);
        self::assertSame(UserRole::User, $user->role);
        self::assertTrue($user->is_active);
        self::assertSame('CZ-OLD', $profile->fresh()->club_member_number);
        self::assertSame(MemberVerificationStatus::Unverified, $profile->fresh()->verification_status);
    }

    public function test_user_changes_email_only_with_current_password_and_unique_address(): void
    {
        $user = User::factory()->create([
            'email' => 'stary@example.com',
            'password' => Hash::make('AktualneHaslo123'),
        ]);

        $this->actingAs($user)
            ->patch(route('account.email.update'), [
                'email' => 'NOWY@EXAMPLE.COM',
                'email_current_password' => 'błędne-hasło',
            ])
            ->assertSessionHasErrors('email_current_password');
        self::assertSame('stary@example.com', $user->fresh()->email);

        User::factory()->create(['email' => 'zajety@example.com']);
        $this->actingAs($user)
            ->patch(route('account.email.update'), [
                'email' => 'zajety@example.com',
                'email_current_password' => 'AktualneHaslo123',
            ])
            ->assertSessionHasErrors([
                'email' => 'Nie można zapisać tego adresu e-mail.',
            ]);
        self::assertSame('stary@example.com', $user->fresh()->email);

        $this->actingAs($user)
            ->patch(route('account.email.update'), [
                'email' => ' NOWY@EXAMPLE.COM ',
                'email_current_password' => 'AktualneHaslo123',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Adres e-mail został zmieniony.');

        self::assertSame('nowy@example.com', $user->fresh()->email);
    }

    public function test_password_change_validates_current_strength_and_confirmation_then_hashes_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('AktualneHaslo123'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'NiepoprawneHaslo123',
                'password' => 'NoweBezpieczneHaslo123',
                'password_confirmation' => 'NoweBezpieczneHaslo123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'AktualneHaslo123',
                'password' => 'slabe',
                'password_confirmation' => 'slabe',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'AktualneHaslo123',
                'password' => 'NoweBezpieczneHaslo123',
                'password_confirmation' => 'InneBezpieczneHaslo123',
            ])
            ->assertSessionHasErrors('password');

        $oldRememberToken = $user->remember_token;
        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'AktualneHaslo123',
                'password' => 'NoweBezpieczneHaslo123',
                'password_confirmation' => 'NoweBezpieczneHaslo123',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Hasło zostało zmienione.');

        $user->refresh();
        self::assertTrue(Hash::check('NoweBezpieczneHaslo123', $user->password));
        self::assertNotSame($oldRememberToken, $user->remember_token);
    }

    public function test_admin_can_create_edit_and_verify_member_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.member-profiles.edit', $user))
            ->assertOk()
            ->assertSeeText($user->name)
            ->assertSeeText('Dane członkowskie');

        $this->actingAs($admin)
            ->put(route('admin.member-profiles.update', $user), [
                'pzss_license_number' => ' pzss-admin-1 ',
                'pzss_license_expires_at' => '2027-12-31',
                'shooting_patent_number' => 'PAT-ADMIN-1',
                'firearm_permit_number' => 'POZ-ADMIN-1',
                'club_member_number' => 'CZ-ADMIN-1',
                'joined_club_year' => 2020,
                'age_category' => MemberAgeCategory::Senior->value,
                'disciplines' => [Discipline::Pistol->value, Discipline::Rifle->value],
                'verification_status' => MemberVerificationStatus::Verified->value,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Dane członkowskie zostały zapisane.');

        $profile = $user->memberProfile()->firstOrFail();
        self::assertSame('PZSS-ADMIN-1', $profile->pzss_license_number);
        self::assertSame(['pistol', 'rifle'], $profile->disciplines);
        self::assertSame(MemberAgeCategory::Senior, $profile->age_category);
        self::assertSame(MemberVerificationStatus::Verified, $profile->verification_status);
        self::assertSame($admin->getKey(), $profile->verified_by);
        self::assertNotNull($profile->verified_at);
        self::assertDatabaseCount('member_profiles', 1);
    }

    public function test_moderator_has_no_member_profile_permission(): void
    {
        $moderator = User::factory()->create(['role' => UserRole::Moderator]);
        $user = User::factory()->create();
        $profile = MemberProfile::factory()->create(['user_id' => $user]);

        self::assertTrue(Gate::forUser($moderator)->denies('view', $profile));
        self::assertTrue(Gate::forUser($moderator)->denies('update', $profile));

        $this->actingAs($moderator)
            ->get(route('admin.member-profiles.edit', $user))
            ->assertForbidden();
        $this->actingAs($moderator)
            ->put(route('admin.member-profiles.update', $user), [
                'verification_status' => MemberVerificationStatus::Verified->value,
            ])
            ->assertForbidden();

        self::assertSame(MemberVerificationStatus::Unverified, $profile->fresh()->verification_status);
    }

    public function test_member_profile_factory_provides_casts_and_one_to_one_relation(): void
    {
        $profile = MemberProfile::factory()->create();

        $user = $profile->user()->firstOrFail();
        self::assertInstanceOf(User::class, $user);
        self::assertSame($profile->getKey(), $user->memberProfile()->firstOrFail()->getKey());
        self::assertIsArray($profile->disciplines);
        self::assertInstanceOf(MemberAgeCategory::class, $profile->age_category);
        self::assertSame(MemberVerificationStatus::Unverified, $profile->verification_status);
        self::assertNotNull($profile->pzss_license_expires_at);
    }
}
