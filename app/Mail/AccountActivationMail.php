<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $businessName,
        public string $activationUrl,
        public int $expiresHours = 48,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('email.from.address'),
            subject: "Create your account — {$this->businessName}",
            replyTo: config('email.reply_to.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderHtml(),
        );
    }

    public function build(): static
    {
        return $this->subject("Create your account — {$this->businessName}")
            ->from(config('email.from.address'), config('email.from.name'))
            ->replyTo(config('email.reply_to.address'), config('email.reply_to.name'));
    }

    private function renderHtml(): string
    {
        return view('emails.account-activation', [
            'businessName' => $this->businessName,
            'activationUrl' => $this->activationUrl,
            'expiresHours' => $this->expiresHours,
        ])->render();
    }

    private function renderPlainText(): string
    {
        $platformName = config('email.from.name', 'HALIS');

        return <<<TEXT
Create your account

Welcome to {$platformName}!

Your business, {$this->businessName}, has been registered. Click the link below to create your admin account and start setting up your POS.

Create Your Account: {$this->activationUrl}

This link expires in {$this->expiresHours} hours.

If you didn't register this business, you can safely ignore this email. No account has been created yet.

---
{$platformName} — Modern POS & Inventory Management
© {$this->expiresHours} {$platformName}. All rights reserved.
TEXT;
    }
}
