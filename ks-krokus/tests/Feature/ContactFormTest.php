<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_is_sent_to_board(): void
    {
        Mail::fake();
        config(['contact.recipient' => 'zarzad@ks-krokus.pl']);

        $response = $this->post(route('contact.send'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'phone' => '+48 500 000 000',
            'subject' => 'Pytanie o trening',
            'message' => 'Proszę o informację dotyczącą najbliższego treningu.',
        ]);

        $response
            ->assertRedirect(route('contact').'#formularz-kontaktowy')
            ->assertSessionHas('success', 'Dziękujemy. Wiadomość została wysłana.');

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail): bool {
            return $mail->hasTo('zarzad@ks-krokus.pl')
                && $mail->hasReplyTo('jan@example.com');
        });
    }

    public function test_contact_form_has_polish_validation_messages(): void
    {
        Mail::fake();

        $this->from(route('contact'))
            ->post(route('contact.send'), [])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors([
                'name' => 'Podaj imię i nazwisko.',
                'email' => 'Podaj adres e-mail.',
                'subject' => 'Podaj temat wiadomości.',
                'message' => 'Wpisz treść wiadomości.',
            ]);

        Mail::assertNothingSent();
    }

    public function test_honeypot_rejects_spam(): void
    {
        Mail::fake();

        $this->post(route('contact.send'), [
            'name' => 'Robot',
            'email' => 'robot@example.com',
            'subject' => 'Spam',
            'message' => 'Automatyczna wiadomość reklamowa.',
            'website' => 'https://spam.example.com',
        ])->assertSessionHasErrors('website');

        Mail::assertNothingSent();
    }

    public function test_inactive_trainers_are_not_shown_publicly(): void
    {
        User::factory()->create([
            'name' => 'Nieaktywny Trener',
            'role' => UserRole::User,
            'is_active' => false,
            'is_trainer' => true,
        ]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSeeText('Nieaktywny Trener');
    }
}
