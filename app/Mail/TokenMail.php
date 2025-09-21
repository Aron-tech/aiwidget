<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $user_name;

    public function __construct(string $token, string $user_name = '')
    {
        $this->token = $token;
        $this->user_name = $user_name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Biztonsági Token - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.token',
            with: [
                'token' => $this->token,
                'userName' => $this->user_name,
                'appName' => config('app.name'),
            ],
        );
    }
}
