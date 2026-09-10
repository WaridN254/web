<?php

namespace App\Jobs;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public Email $email,
        public EmailAccount $account,
    ) {
        $this->queue = 'email-send';
    }

    public function handle(): void
    {
        $smtpHost = $this->account->smtp_host;
        $smtpPort = $this->account->smtp_port;
        $smtpUsername = $this->account->smtp_username;
        $smtpPassword = Crypt::decryptString($this->account->smtp_password);
        $smtpEncryption = $this->account->smtp_encryption;

        $tls = match ($smtpEncryption) {
            'ssl' => true,
            default => false,
        };

        $transport = new EsmtpTransport($smtpHost, $smtpPort, $tls);
        $transport->setUsername($smtpUsername);
        $transport->setPassword($smtpPassword);

        $mailer = new Mailer($transport);

        $symfonyEmail = $this->buildSymfonyEmail();

        $mailer->send($symfonyEmail);

        $this->email->update([
            'sent_at' => now(),
            'folder' => 'sent',
        ]);

        Log::info('Email sent successfully', [
            'email_id' => $this->email->id,
            'account_id' => $this->account->id,
        ]);
    }

    private function buildSymfonyEmail(): SymfonyEmail
    {
        $email = new SymfonyEmail();

        $fromName = $this->account->display_name ?? $this->account->email_address;
        $email->from(new Address($this->account->email_address, $fromName));

        $email->subject($this->email->subject);

        if ($this->email->body_text) {
            $email->text($this->email->body_text);
        }

        if ($this->email->body_html) {
            $email->html($this->email->body_html);
        }

        $toRecipients = $this->email->recipients()->where('type', 'to')->get();
        foreach ($toRecipients as $recipient) {
            $email->to(new Address(
                $recipient->email_address,
                $recipient->name ?? ''
            ));
        }

        $ccRecipients = $this->email->recipients()->where('type', 'cc')->get();
        foreach ($ccRecipients as $ccRecipient) {
            $email->cc(new Address(
                $ccRecipient->email_address,
                $ccRecipient->name ?? ''
            ));
        }

        $bccRecipients = $this->email->recipients()->where('type', 'bcc')->get();
        foreach ($bccRecipients as $bccRecipient) {
            $email->addBcc(new Address(
                $bccRecipient->email_address,
                $bccRecipient->name ?? ''
            ));
        }

        if ($this->email->in_reply_to) {
            $email->addHeader('In-Reply-To', '<' . $this->email->in_reply_to . '>');
            $email->addHeader('References', '<' . $this->email->in_reply_to . '>');
        }

        if ($this->email->has_attachments) {
            $attachments = $this->email->attachments()->get();
            foreach ($attachments as $attachment) {
                $this->attachFile($email, $attachment);
            }
        }

        return $email;
    }

    private function attachFile(SymfonyEmail $email, EmailAttachment $attachment): void
    {
        $content = null;
        $filename = $attachment->original_name;
        $mimeType = $attachment->mime_type ?? 'application/octet-stream';

        // Try storage_path first (relative to disk)
        if (!empty($attachment->storage_path)) {
            $disk = Storage::disk($attachment->storage_disk ?? 'local');
            if ($disk->exists($attachment->storage_path)) {
                $content = $disk->get($attachment->storage_path);
            }
        }

        // Fallback to file_path (absolute)
        if ($content === null && !empty($attachment->file_path) && file_exists($attachment->file_path)) {
            $content = file_get_contents($attachment->file_path);
        }

        // Fallback to storage local absolute path
        if ($content === null) {
            $absolutePath = storage_path('app/' . ($attachment->storage_path ?? ''));
            if (file_exists($absolutePath)) {
                $content = file_get_contents($absolutePath);
            }
        }

        if ($content === null) {
            Log::warning('Attachment file not found', [
                'attachment_id' => $attachment->id,
                'storage_path' => $attachment->storage_path ?? null,
                'file_path' => $attachment->file_path ?? null,
            ]);
            return;
        }

        if ($attachment->is_inline && $attachment->content_id) {
            $email->addPart(
                $content,
                $mimeType,
                $filename,
                'inline'
            );
        } else {
            $email->attachFromContent(
                $content,
                $filename,
                $mimeType
            );
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendEmailJob failed', [
            'email_id' => $this->email->id,
            'account_id' => $this->account->id,
            'error' => $exception?->getMessage(),
        ]);

        $this->email->update([
            'is_draft' => true,
        ]);
    }
}
