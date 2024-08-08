<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $otp;

    public function __construct(string $email, string $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('app.mail_address'),
            to: $this->email,
            subject: 'Verify Mail'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.otp',
            with: ['email' => $this->email, 'code' => $this->otp]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

