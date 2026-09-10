<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('email.from.address'),
            subject: 'HALIS — Test Email',
            replyTo: config('email.reply_to.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderHtml(),
        );
    }

    private function renderHtml(): string
    {
        $appName = config('email.from.name', 'HALIS');
        $mailer = config('mail.default');
        $from = config('email.from.address');
        $queue = config('queue.default');

        return <<<HTML
        <!DOCTYPE html>
        <html><head><meta charset="utf-8"></head>
        <body style="font-family:system-ui,-apple-system,sans-serif;color:#1a1a2e;max-width:560px;margin:0 auto;padding:40px 20px">
            <div style="text-align:center;margin-bottom:24px">
                <div style="background:#6366f1;border-radius:8px;padding:8px 14px;display:inline-block">
                    <span style="color:#fff;font-size:18px;font-weight:800">{$appName}</span>
                </div>
            </div>
            <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:12px">Email is working!</h2>
            <p style="font-size:.95rem;color:#4a4a68;line-height:1.7;margin-bottom:16px">
                This is a test email from your {$appName} platform. If you received this, your transactional email infrastructure is configured correctly.
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                <tr><td style="padding:10px 16px;background:#f9fafb;font-size:13px;color:#6b7280;border-bottom:1px solid #e5e7eb"><strong>Mailer</strong></td><td style="padding:10px 16px;font-size:13px">{$mailer}</td></tr>
                <tr><td style="padding:10px 16px;background:#f9fafb;font-size:13px;color:#6b7280;border-bottom:1px solid #e5e7eb"><strong>From</strong></td><td style="padding:10px 16px;font-size:13px">{$from}</td></tr>
                <tr><td style="padding:10px 16px;background:#f9fafb;font-size:13px;color:#6b7280"><strong>Queue</strong></td><td style="padding:10px 16px;font-size:13px">{$queue}</td></tr>
            </table>
            <hr style="border:none;border-top:1px solid #f0f0f5;margin:24px 0">
            <p style="font-size:.8rem;color:#c7c7cc;text-align:center">{$appName} — Modern POS & Inventory Management</p>
        </body></html>
        HTML;
    }

    private function renderPlainText(): string
    {
        $appName = config('email.from.name', 'HALIS');
        $mailer = config('mail.default');
        $from = config('email.from.address');

        return <<<TEXT
Email is working!

This is a test email from your {$appName} platform. If you received this, your transactional email infrastructure is configured correctly.

Mailer: {$mailer}
From: {$from}

---
{$appName} — Modern POS & Inventory Management
TEXT;
    }
}
