<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactMessage extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{name: string, email: string, phone?: string|null, subject: string, message: string}  $formData
     */
    public function __construct(public readonly array $formData)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $subject = str_replace(["\r", "\n"], ' ', $this->formData['subject']);

        return new Envelope(
            from: new Address(
                (string) config('mail.from.address'),
                (string) config('mail.from.name'),
            ),
            replyTo: [new Address($this->formData['email'], $this->formData['name'])],
            subject: '[KS Krokus] '.$subject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact');
    }
}
