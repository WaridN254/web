<?php

namespace App\Jobs;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailRecipient;
use App\Models\EmailThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SyncEmailAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public EmailAccount $account,
    ) {
        $this->queue = 'email-sync';
    }

    public function handle(): void
    {
        $imapHost = $this->account->imap_host;
        $imapPort = $this->account->imap_port;
        $imapUsername = $this->account->imap_username;
        $imapPassword = Crypt::decryptString($this->account->imap_password);

        $flags = '/imap/ssl/validate-cert';
        $mailbox = sprintf('{%s:%d%s}%s', $imapHost, $imapPort, $flags, 'INBOX');

        $connection = @imap_open($mailbox, $imapUsername, $imapPassword);

        if ($connection === false) {
            $error = imap_last_error();
            $this->account->update([
                'connection_status' => 'error',
                'last_connection_error' => $error,
            ]);

            Log::error('IMAP connection failed', [
                'account_id' => $this->account->id,
                'error' => $error,
            ]);

            return;
        }

        $this->account->update([
            'connection_status' => 'connected',
            'last_connection_error' => null,
        ]);

        try {
            $sinceDate = $this->account->last_synced_at
                ? $this->account->last_synced_at->subDay()->format('d-M-Y')
                : now()->subDays(30)->format('d-M-Y');

            $searchCriteria = 'SINCE ' . $sinceDate;
            $messageIds = imap_search($connection, $searchCriteria);

            if ($messageIds === false || empty($messageIds)) {
                Log::info('No new messages found', ['account_id' => $this->account->id]);
                $this->account->update(['last_synced_at' => now()]);
                return;
            }

            foreach ($messageIds as $messageId) {
                try {
                    $this->processMessage($connection, (int) $messageId);
                } catch (Throwable $e) {
                    Log::error('Failed to process email message', [
                        'account_id' => $this->account->id,
                        'message_id' => $messageId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->account->update(['last_synced_at' => now()]);

            $unreadCount = Email::where('email_account_id', $this->account->id)
                ->where('folder', 'inbox')
                ->where('is_read', false)
                ->count();

            $this->account->update(['unread_count' => $unreadCount]);
        } finally {
            imap_close($connection);
        }
    }

    private function processMessage($connection, int $messageId): void
    {
        $header = imap_headerinfo($connection, $messageId);
        $structure = imap_fetchstructure($connection, $messageId);

        $externalMessageId = $this->extractMessageId($header);
        $externalUid = imap_uid($connection, $messageId);

        $existingEmail = Email::where('email_account_id', $this->account->id)
            ->where('external_message_id', $externalMessageId)
            ->first();

        if ($existingEmail) {
            return;
        }

        $fromName = isset($header->from[0]->personal)
            ? imap_utf8($header->from[0]->personal)
            : null;
        $fromAddress = $header->from[0]->mailbox . '@' . $header->from[0]->host;

        $bodyText = null;
        $bodyHtml = null;

        if (isset($structure->parts)) {
            foreach ($structure->parts as $part) {
                $this->fetchPart($connection, $messageId, $part, $bodyText, $bodyHtml);
            }
        } else {
            $encoding = $this->getEncoding($structure);
            $body = imap_body($connection, $messageId);
            $body = $this->decodeBody($body, $encoding);

            if ($structure->subtype === 'PLAIN') {
                $bodyText = $body;
            } else {
                $bodyHtml = $body;
            }
        }

        $subject = isset($header->subject)
            ? imap_utf8($header->subject)
            : '(No Subject)';

        $receivedAt = isset($header->date)
            ? ($this->parseDate($header->date) ?? now())
            : now();

        $thread = $this->findOrCreateThread($subject);

        $email = Email::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->account->tenant_id,
            'email_account_id' => $this->account->id,
            'thread_id' => $thread->id,
            'external_message_id' => $externalMessageId,
            'external_uid' => $externalUid,
            'in_reply_to' => $header->in_reply_to ?? null,
            'references' => isset($header->references)
                ? implode(',', (array) $header->references)
                : null,
            'from_name' => $fromName,
            'from_address' => $fromAddress,
            'subject' => $subject,
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
            'folder' => 'inbox',
            'is_read' => (isset($header->Unseen) && $header->Unseen === false),
            'has_attachments' => $this->hasAttachments($structure),
            'attachment_count' => $this->countAttachments($structure),
            'received_at' => $receivedAt,
        ]);

        $this->createRecipients($email, $header);

        $this->processAttachments($connection, $messageId, $structure, $email);

        $thread->update([
            'last_message_at' => $receivedAt,
            'message_count' => $thread->emails()->count(),
        ]);
    }

    private function fetchPart($connection, int $messageId, object $part, ?string &$bodyText, ?string &$bodyHtml): void
    {
        $encoding = $this->getEncoding($part);
        $partNumber = $part->part ?? '1';
        $body = imap_fetchbody($connection, $messageId, $partNumber);
        $body = $this->decodeBody($body, $encoding);

        if ($part->type === 0) {
            if (strtoupper($part->subtype) === 'PLAIN') {
                $bodyText = $body;
            } elseif (strtoupper($part->subtype) === 'HTML') {
                $bodyHtml = $body;
            }
        }

        if (isset($part->parts)) {
            foreach ($part->parts as $subPart) {
                $this->fetchPart($connection, $messageId, $subPart, $bodyText, $bodyHtml);
            }
        }
    }

    private function getEncoding(object $part): int
    {
        return $part->encoding ?? 0;
    }

    private function decodeBody(string $body, int $encoding): string
    {
        return match ($encoding) {
            0 => $body,
            1 => quoted_printable_decode($body),
            2 => imap_binary($body),
            3 => base64_decode($body),
            4 => imap_binary($body),
            5 => quoted_printable_decode($body),
            default => $body,
        };
    }

    private function extractMessageId(object $header): ?string
    {
        if (!empty($header->message_id)) {
            return trim($header->message_id, '<>');
        }

        return null;
    }

    private function hasAttachments(object $structure): bool
    {
        if (empty($structure->parts)) {
            return $structure->type !== 0;
        }

        foreach ($structure->parts as $part) {
            if ($part->type !== 0) {
                return true;
            }
            if (isset($part->parts) && $this->hasAttachments($part)) {
                return true;
            }
        }

        return false;
    }

    private function countAttachments(object $structure): int
    {
        $count = 0;

        if (empty($structure->parts)) {
            return $structure->type !== 0 ? 1 : 0;
        }

        foreach ($structure->parts as $part) {
            if ($part->type !== 0) {
                $count++;
            }
            if (isset($part->parts)) {
                $count += $this->countAttachments($part);
            }
        }

        return $count;
    }

    private function findOrCreateThread(string $subject): EmailThread
    {
        $normalizedSubject = preg_replace('/^(Re|Fw|Fwd|RE|FW|FWD)[:\s]+/i', '', $subject);
        $normalizedSubject = trim($normalizedSubject);

        $normalizedLower = strtolower($normalizedSubject);

        $thread = EmailThread::where('email_account_id', $this->account->id)
            ->whereRaw('TRIM(LOWER(REPLACE(REPLACE(REPLACE(subject, ?, ?), ?, ?), ?, ?))) = ?', [
                'Re:', '', 'Fw:', '', 'Fwd:', '', $normalizedLower,
            ])
            ->latest('last_message_at')
            ->first();

        if (!$thread) {
            $thread = EmailThread::create([
                'id' => Str::uuid(),
                'tenant_id' => $this->account->tenant_id,
                'email_account_id' => $this->account->id,
                'subject' => $subject,
                'folder' => 'inbox',
                'message_count' => 0,
            ]);
        }

        return $thread;
    }

    private function createRecipients(Email $email, object $header): void
    {
        $recipientTypes = [
            'to' => $header->to ?? [],
            'cc' => $header->cc ?? [],
            'bcc' => $header->bcc ?? [],
        ];

        foreach ($recipientTypes as $type => $recipients) {
            foreach ($recipients as $recipient) {
                EmailRecipient::create([
                    'id' => Str::uuid(),
                    'email_id' => $email->id,
                    'type' => $type,
                    'name' => isset($recipient->personal) ? imap_utf8($recipient->personal) : null,
                    'email_address' => $recipient->mailbox . '@' . $recipient->host,
                ]);
            }
        }
    }

    private function processAttachments($connection, int $messageId, object $structure, Email $email): void
    {
        if (empty($structure->parts)) {
            return;
        }

        foreach ($structure->parts as $part) {
            if ($part->type !== 0) {
                $partNumber = $part->part ?? '1';
                $partStructure = $part;
                $fileName = $this->getAttachmentFileName($partStructure);
                $mimeType = $this->getMimeType($partStructure);
                $content = imap_fetchbody($connection, $messageId, $partNumber);

                $encoding = $this->getEncoding($partStructure);
                $content = $this->decodeBody($content, $encoding);

                $storagePath = 'email-attachments/' . $email->id . '/' . $fileName;
                Storage::disk('local')->put($storagePath, $content);

                EmailAttachment::create([
                    'id' => Str::uuid(),
                    'tenant_id' => $this->account->tenant_id,
                    'email_id' => $email->id,
                    'file_name' => $fileName,
                    'original_name' => $fileName,
                    'mime_type' => $mimeType,
                    'file_size' => strlen($content),
                    'storage_disk' => 'local',
                    'storage_path' => $storagePath,
                    'content_id' => $this->getContentId($partStructure),
                    'is_inline' => !empty($partStructure->disposition) && $partStructure->disposition === 'inline',
                ]);
            }

            if (isset($part->parts)) {
                $this->processAttachments($connection, $messageId, $part, $email);
            }
        }
    }

    private function getAttachmentFileName(object $part): string
    {
        if (!empty($part->dparameters)) {
            foreach ($part->dparameters as $param) {
                if (strtolower($param->attribute) === 'filename') {
                    return imap_utf8($param->value);
                }
            }
        }

        if (!empty($part->parameters)) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    return imap_utf8($param->value);
                }
            }
        }

        return 'attachment_' . Str::random(8);
    }

    private function getMimeType(object $part): string
    {
        $type = match ($part->type) {
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'video',
            6 => 'image',
            7 => 'chemical',
            default => 'application',
        };

        return $type . '/' . strtolower($part->subtype ?? 'octet-stream');
    }

    private function getContentId(object $part): ?string
    {
        if (!empty($part->parameters)) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) === 'content-id') {
                    return trim($param->value, '<>');
                }
            }
        }

        return null;
    }

    private function parseDate(string $dateString): ?Carbon
    {
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            $formats = [
                'D, j M Y H:i:s O',
                'D, j M Y H:i:s',
                'j M Y H:i:s O',
                'Y-m-d H:i:s',
            ];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateString);
                } catch (\Exception $ex) {
                    continue;
                }
            }
            return null;
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SyncEmailAccountJob failed', [
            'account_id' => $this->account->id,
            'error' => $exception?->getMessage(),
        ]);

        $this->account->update([
            'connection_status' => 'error',
            'last_connection_error' => $exception?->getMessage(),
        ]);
    }
}
