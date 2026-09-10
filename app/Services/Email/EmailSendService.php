<?php

namespace App\Services\Email;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailRecipient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailSendService
{
    public function send(array $data, string $tenantId): Email
    {
        $account = EmailAccount::where('tenant_id', $tenantId)
            ->where('id', $data['account_id'])
            ->where('connection_status', 'connected')
            ->firstOrFail();

        $email = $this->createEmailRecord($data, $account, $tenantId, 'sent');

        try {
            $this->sendViaSmtp($account, $email);

            $email->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email ' . $email->id, [
                'error' => $e->getMessage(),
            ]);

            $email->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        if (!empty($data['attachments'])) {
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function saveDraft(array $data, string $tenantId): Email
    {
        $account = EmailAccount::where('tenant_id', $tenantId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $email = $this->createEmailRecord($data, $account, $tenantId, 'draft');

        if (!empty($data['attachments'])) {
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function updateDraft(string $draftId, array $data, string $tenantId): Email
    {
        $email = Email::where('tenant_id', $tenantId)
            ->where('id', $draftId)
            ->where('status', 'draft')
            ->firstOrFail();

        $email->update([
            'subject' => $data['subject'] ?? $email->subject,
            'body' => $data['body'] ?? $email->body,
            'body_html' => $data['body_html'] ?? $email->body_html,
        ]);

        if (isset($data['recipients'])) {
            $email->recipients()->delete();
            $this->createRecipients($email, $data['recipients']);
        }

        if (isset($data['attachments'])) {
            $email->attachments()->delete();
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function reply(string $emailId, array $data, string $tenantId): Email
    {
        $originalEmail = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $replyTo = $originalEmail->recipients()
            ->where('type', 'from')
            ->pluck('email_address')
            ->toArray();

        $account = EmailAccount::where('tenant_id', $tenantId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $replyData = array_merge($data, [
            'subject' => $this->prefixSubject($originalEmail->subject, 'Re:'),
            'body' => $data['body'] ?? '',
            'body_html' => $data['body_html'] ?? $data['body'] ?? '',
            'in_reply_to' => $originalEmail->message_id,
            'thread_id' => $originalEmail->thread_id ?? $originalEmail->id,
            'recipients' => $this->buildReplyRecipients($replyTo, $data['recipients'] ?? []),
            'account_id' => $account->id,
        ]);

        $email = $this->createEmailRecord($replyData, $account, $tenantId, 'sent');

        try {
            $this->sendViaSmtp($account, $email);
            $email->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $email->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }

        if (!empty($data['attachments'])) {
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function replyAll(string $emailId, array $data, string $tenantId): Email
    {
        $originalEmail = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $fromAddresses = $originalEmail->recipients()
            ->where('type', 'from')
            ->pluck('email_address')
            ->toArray();

        $ccAddresses = $originalEmail->recipients()
            ->where('type', 'cc')
            ->pluck('email_address')
            ->toArray();

        $allRecipients = array_merge($fromAddresses, $ccAddresses);

        $account = EmailAccount::where('tenant_id', $tenantId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $replyData = array_merge($data, [
            'subject' => $this->prefixSubject($originalEmail->subject, 'Re:'),
            'body' => $data['body'] ?? '',
            'body_html' => $data['body_html'] ?? $data['body'] ?? '',
            'in_reply_to' => $originalEmail->message_id,
            'thread_id' => $originalEmail->thread_id ?? $originalEmail->id,
            'recipients' => $this->buildReplyRecipients($allRecipients, $data['recipients'] ?? []),
            'account_id' => $account->id,
        ]);

        $email = $this->createEmailRecord($replyData, $account, $tenantId, 'sent');

        try {
            $this->sendViaSmtp($account, $email);
            $email->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $email->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }

        if (!empty($data['attachments'])) {
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function forward(string $emailId, array $data, string $tenantId): Email
    {
        $originalEmail = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $account = EmailAccount::where('tenant_id', $tenantId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $forwardedBody = $this->buildForwardBody($originalEmail, $data['body'] ?? '');

        $forwardData = array_merge($data, [
            'subject' => $this->prefixSubject($originalEmail->subject, 'Fwd:'),
            'body' => $forwardedBody,
            'body_html' => $data['body_html'] ?? $forwardedBody,
            'in_reply_to' => $originalEmail->message_id,
            'thread_id' => $originalEmail->thread_id ?? $originalEmail->id,
            'recipients' => $data['recipients'] ?? [],
            'account_id' => $account->id,
        ]);

        $email = $this->createEmailRecord($forwardData, $account, $tenantId, 'sent');

        try {
            $this->sendViaSmtp($account, $email);
            $email->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $email->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }

        if (!empty($data['attachments'])) {
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function forwardAsAttachment(string $emailId, array $data, string $tenantId): Email
    {
        $originalEmail = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $account = EmailAccount::where('tenant_id', $tenantId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $forwardData = array_merge($data, [
            'subject' => $this->prefixSubject($originalEmail->subject, 'Fwd:'),
            'body' => $data['body'] ?? '',
            'body_html' => $data['body_html'] ?? $data['body'] ?? '',
            'thread_id' => Str::uuid()->toString(),
            'recipients' => $data['recipients'] ?? [],
            'account_id' => $account->id,
        ]);

        $email = $this->createEmailRecord($forwardData, $account, $tenantId, 'sent');

        $this->attachOriginalAsFile($email, $originalEmail, $tenantId);

        try {
            $this->sendViaSmtp($account, $email);
            $email->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $email->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }

        if (!empty($data['attachments'])) {
            $this->storeAttachments($email, $data['attachments']);
        }

        return $email->fresh(['recipients', 'attachments', 'labels']);
    }

    public function sendViaSmtp(EmailAccount $account, Email $email): void
    {
        $password = Crypt::decryptString($account->smtp_password);

        $tls = match ($account->smtp_encryption) {
            'ssl' => true,
            default => false,
        };

        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            $account->smtp_host,
            $account->smtp_port,
            $tls,
        );

        $transport->setUsername($account->smtp_username);
        $transport->setPassword($password);

        $mailer = new \Symfony\Component\Mailer\Mailer($transport);

        $message = (new \Symfony\Component\Mime\Email())
            ->from($account->from_name ? $account->from_name . ' <' . $account->email_address . '>' : $account->email_address)
            ->subject($email->subject)
            ->text($email->body);

        if ($email->body_html) {
            $message->html($email->body_html);
        }

        $recipients = $email->recipients;
        $fromRecipients = $recipients->where('type', 'to');
        foreach ($fromRecipients as $recipient) {
            $message->to($recipient->email_address);
        }

        $ccRecipients = $recipients->where('type', 'cc');
        foreach ($ccRecipients as $recipient) {
            $message->cc($recipient->email_address);
        }

        $bccRecipients = $recipients->where('type', 'bcc');
        foreach ($bccRecipients as $recipient) {
            $message->bcc($recipient->email_address);
        }

        $attachments = $email->attachments;
        foreach ($attachments as $attachment) {
            $content = null;

            // Try storage_path first
            if (!empty($attachment->storage_path)) {
                $disk = Storage::disk($attachment->storage_disk ?? 'local');
                if ($disk->exists($attachment->storage_path)) {
                    $content = $disk->get($attachment->storage_path);
                }
            }

            // Fallback to file_path
            if ($content === null && !empty($attachment->file_path) && file_exists($attachment->file_path)) {
                $content = file_get_contents($attachment->file_path);
            }

            if ($content !== null) {
                $message->attachFromContent($content, $attachment->original_name, $attachment->mime_type);
            }
        }

        if ($email->in_reply_to) {
            $message->header('In-Reply-To', $email->in_reply_to);
            $message->header('References', $email->in_reply_to);
        }

        $message->header('X-Mailer', 'Laravel Email Service');
        $message->header('Message-ID', $email->message_id);

        $mailer->send($message);
    }

    public function storeAttachments(Email $email, array $files): void
    {
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $tenantId = $email->tenant_id;
            $directory = 'emails/' . $tenantId;
            $filename = Str::uuid() . '_' . $file->getClientOriginalName();

            Storage::disk('local')->putFile($directory, $file, $filename);

            EmailAttachment::create([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $tenantId,
                'email_id' => $email->id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $filename,
                'storage_disk' => 'local',
                'storage_path' => $directory . '/' . $filename,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }
    }

    private function createEmailRecord(array $data, EmailAccount $account, string $tenantId, string $status): Email
    {
        $email = Email::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'account_id' => $account->id,
            'user_id' => auth()->user()->id,
            'message_id' => $data['message_id'] ?? Str::uuid()->toString() . '@' . $account->email_address,
            'thread_id' => $data['thread_id'] ?? Str::uuid()->toString(),
            'subject' => $data['subject'] ?? '',
            'body' => $data['body'] ?? '',
            'body_html' => $data['body_html'] ?? null,
            'status' => $status,
            'folder' => $status === 'sent' ? 'sent' : 'drafts',
            'is_starred' => false,
            'is_important' => false,
            'is_read' => true,
            'in_reply_to' => $data['in_reply_to'] ?? null,
        ]);

        $this->createRecipients($email, $data['recipients'] ?? []);

        return $email;
    }

    private function createRecipients(Email $email, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            EmailRecipient::create([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $email->tenant_id,
                'email_id' => $email->id,
                'email_address' => $recipient['email_address'],
                'name' => $recipient['name'] ?? null,
                'type' => $recipient['type'] ?? 'to',
            ]);
        }
    }

    private function prefixSubject(string $subject, string $prefix): string
    {
        if (str_starts_with($subject, $prefix)) {
            return $subject;
        }

        return $prefix . ' ' . $subject;
    }

    private function buildReplyRecipients(array $originalRecipients, array $additionalRecipients): array
    {
        $recipients = [];
        foreach ($originalRecipients as $email) {
            $recipients[] = [
                'email_address' => $email,
                'type' => 'to',
            ];
        }

        foreach ($additionalRecipients as $recipient) {
            $recipients[] = [
                'email_address' => $recipient['email_address'] ?? $recipient,
                'type' => $recipient['type'] ?? 'to',
            ];
        }

        return $recipients;
    }

    private function buildForwardBody(Email $originalEmail, string $additionalBody): string
    {
        $from = $originalEmail->recipients->where('type', 'first')->first()?->email_address ?? 'Unknown';
        $date = $originalEmail->created_at->format('M d, Y H:i');
        $to = $originalEmail->recipients->where('type', 'to')->pluck('email_address')->join(', ');

        $body = $additionalBody ? $additionalBody . "\n\n---------- Forwarded message ----------\n" : "---------- Forwarded message ----------\n";
        $body .= "From: " . $from . "\n";
        $body .= "Date: " . $date . "\n";
        $body .= "Subject: " . $originalEmail->subject . "\n";
        $body .= "To: " . $to . "\n\n";
        $body .= $originalEmail->body;

        return $body;
    }

    private function attachOriginalAsFile(Email $email, Email $originalEmail, string $tenantId): void
    {
        $directory = 'emails/' . $tenantId;

        $content = "From: " . ($originalEmail->recipients->where('type', 'from')->first()?->email_address ?? 'Unknown') . "\n";
        $content .= "Date: " . $originalEmail->created_at->format('M d, Y H:i') . "\n";
        $content .= "Subject: " . $originalEmail->subject . "\n";
        $content .= "To: " . $originalEmail->recipients->where('type', 'to')->pluck('email_address')->join(', ') . "\n\n";
        $content .= $originalEmail->body;

        $filename = Str::uuid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalEmail->subject) . '.eml';

        Storage::disk('local')->put($directory . '/' . $filename, $content);

        EmailAttachment::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'email_id' => $email->id,
            'original_name' => $originalEmail->subject . '.eml',
            'file_name' => $filename,
            'storage_disk' => 'local',
            'storage_path' => $directory . '/' . $filename,
            'file_size' => strlen($content),
            'mime_type' => 'message/rfc822',
        ]);
    }
}
