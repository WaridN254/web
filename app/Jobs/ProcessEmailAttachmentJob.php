<?php

namespace App\Jobs;

use App\Models\EmailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessEmailAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public EmailAttachment $attachment,
    ) {
        $this->queue = 'email-sync';
    }

    public function handle(): void
    {
        $disk = Storage::disk($this->attachment->storage_disk);

        if ($disk->exists($this->attachment->storage_path)) {
            return;
        }

        if (!$this->attachment->email) {
            Log::warning('Attachment has no associated email', [
                'attachment_id' => $this->attachment->id,
            ]);
            return;
        }

        $email = $this->attachment->email;

        if (!$email || empty($email->external_uid)) {
            Log::warning('Attachment has no IMAP identifiers', [
                'attachment_id' => $this->attachment->id,
            ]);
            return;
        }

        $this->downloadFromImap($disk);
    }

    private function downloadFromImap($disk): void
    {
        $account = $this->attachment->email->account;

        if (!$account) {
            Log::error('Email account not found for attachment', [
                'attachment_id' => $this->attachment->id,
            ]);
            return;
        }

        $imapHost = $account->imap_host;
        $imapPort = $account->imap_port;
        $imapUsername = $account->imap_username;
        $imapPassword = \Illuminate\Support\Facades\Crypt::decryptString($account->imap_password);

        $flags = '/imap/ssl/validate-cert';
        $mailbox = sprintf('{%s:%d%s}%s', $imapHost, $imapPort, $flags, 'INBOX');

        $connection = @imap_open($mailbox, $imapUsername, $imapPassword);

        if ($connection === false) {
            $error = imap_last_error();
            Log::error('IMAP connection failed for attachment download', [
                'attachment_id' => $this->attachment->id,
                'error' => $error,
            ]);
            return;
        }

        try {
            $externalUid = $this->attachment->email->external_uid;
            $messageNumber = imap_msgno($connection, (int) $externalUid);

            if ($messageNumber === false) {
                Log::error('Message not found for attachment', [
                    'attachment_id' => $this->attachment->id,
                    'external_uid' => $externalUid,
                ]);
                return;
            }

            $partNumber = $this->findPartNumberByFileName($connection, $messageNumber, $this->attachment->original_name) ?? '1';
            $content = imap_fetchbody($connection, $messageNumber, $partNumber);

            if ($content === false) {
                Log::error('Failed to fetch attachment body', [
                    'attachment_id' => $this->attachment->id,
                ]);
                return;
            }

            $structure = imap_fetchstructure($connection, $messageNumber);
            $partData = $this->findPart($structure, $partNumber);

            if ($partData) {
                $content = $this->decodeContent($content, $partData->encoding ?? 0);
            }

            $disk->put($this->attachment->storage_path, $content);

            $this->attachment->update([
                'file_size' => strlen($content),
            ]);

            Log::info('Attachment downloaded successfully', [
                'attachment_id' => $this->attachment->id,
                'path' => $this->attachment->storage_path,
                'size' => strlen($content),
            ]);
        } finally {
            imap_close($connection);
        }
    }

    private function findPartNumberByFileName($connection, int $messageNumber, string $fileName): ?string
    {
        $structure = imap_fetchstructure($connection, $messageNumber);

        if (empty($structure->parts)) {
            return null;
        }

        return $this->searchPartsForFileName($structure, $fileName, '');
    }

    private function searchPartsForFileName(object $structure, string $fileName, string $prefix): ?string
    {
        foreach ($structure->parts as $index => $part) {
            $partPrefix = $prefix ? $prefix . '.' . ($index + 1) : (string) ($index + 1);

            if (!empty($part->dparameters)) {
                foreach ($part->dparameters as $param) {
                    if (strtolower($param->attribute) === 'filename' && imap_utf8($param->value) === $fileName) {
                        return $partPrefix;
                    }
                }
            }

            if (!empty($part->parameters)) {
                foreach ($part->parameters as $param) {
                    if (strtolower($param->attribute) === 'name' && imap_utf8($param->value) === $fileName) {
                        return $partPrefix;
                    }
                }
            }

            if (isset($part->parts)) {
                $found = $this->searchPartsForFileName($part, $fileName, $partPrefix);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function findPart(object $structure, string $partNumber): ?object
    {
        if (!isset($structure->parts)) {
            return $structure;
        }

        $parts = explode('.', $partNumber);
        $current = $structure;

        foreach ($parts as $index) {
            $partIndex = (int) $index - 1;

            if (!isset($current->parts[$partIndex])) {
                return null;
            }

            $current = $current->parts[$partIndex];
        }

        return $current;
    }

    private function decodeContent(string $content, int $encoding): string
    {
        return match ($encoding) {
            0 => $content,
            1 => quoted_printable_decode($content),
            2 => imap_binary($content),
            3 => base64_decode($content),
            4 => imap_binary($content),
            5 => quoted_printable_decode($content),
            default => $content,
        };
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessEmailAttachmentJob failed', [
            'attachment_id' => $this->attachment->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
