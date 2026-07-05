<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $newEmail;

    public function __construct($code, $newEmail)
    {
        $this->code = $code;
        $this->newEmail = $newEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de changement d\'email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
        );
    }
}
