<?php

namespace App\Services\Email;

use App\Models\Email;
use App\Models\EmailAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailFolderService
{
    public function getFolders(string $tenantId, string $accountId): array
    {
        $baseQuery = Email::where('tenant_id', $tenantId)
            ->where('account_id', $accountId);

        $folders = [
            'inbox' => [
                'name' => 'Inbox',
                'folder' => 'inbox',
                'count' => 0,
                'unread_count' => 0,
            ],
            'sent' => [
                'name' => 'Sent',
                'folder' => 'sent',
                'count' => 0,
                'unread_count' => 0,
            ],
            'drafts' => [
                'name' => 'Drafts',
                'folder' => 'drafts',
                'count' => 0,
                'unread_count' => 0,
            ],
            'starred' => [
                'name' => 'Starred',
                'folder' => null,
                'count' => 0,
                'unread_count' => 0,
            ],
            'important' => [
                'name' => 'Important',
                'folder' => null,
                'count' => 0,
                'unread_count' => 0,
            ],
            'spam' => [
                'name' => 'Spam',
                'folder' => 'spam',
                'count' => 0,
                'unread_count' => 0,
            ],
            'trash' => [
                'name' => 'Trash',
                'folder' => 'trash',
                'count' => 0,
                'unread_count' => 0,
            ],
            'archive' => [
                'name' => 'Archive',
                'folder' => 'archive',
                'count' => 0,
                'unread_count' => 0,
            ],
        ];

        $folderCounts = clone $baseQuery;
        $counts = $folderCounts
            ->select('folder', DB::raw('count(*) as count'), DB::raw('sum(case when is_read = false then 1 else 0 end) as unread_count'))
            ->groupBy('folder')
            ->get()
            ->keyBy('folder');

        foreach ($folders as $key => &$folder) {
            if ($folder['folder'] && isset($counts[$folder['folder']])) {
                $folder['count'] = (int) $counts[$folder['folder']]->count;
                $folder['unread_count'] = (int) $counts[$folder['folder']]->unread_count;
            }
        }

        $starredCount = (clone $baseQuery)->where('is_starred', true)->count();
        $starredUnread = (clone $baseQuery)->where('is_starred', true)->where('is_read', false)->count();
        $folders['starred']['count'] = $starredCount;
        $folders['starred']['unread_count'] = $starredUnread;

        $importantCount = (clone $baseQuery)->where('is_important', true)->count();
        $importantUnread = (clone $baseQuery)->where('is_important', true)->where('is_read', false)->count();
        $folders['important']['count'] = $importantCount;
        $folders['important']['unread_count'] = $importantUnread;

        return array_values($folders);
    }

    public function moveToFolder(string $emailId, string $folder, string $tenantId): void
    {
        $email = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $validFolders = ['inbox', 'sent', 'drafts', 'spam', 'trash', 'archive'];

        if (!in_array($folder, $validFolders)) {
            throw new \InvalidArgumentException("Invalid folder: {$folder}");
        }

        $email->update(['folder' => $folder]);
    }

    public function archive(string $emailId, string $tenantId): void
    {
        $this->moveToFolder($emailId, 'archive', $tenantId);
    }

    public function trash(string $emailId, string $tenantId): void
    {
        $email = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $email->update([
            'folder' => 'trash',
            'deleted_at' => now(),
        ]);
    }

    public function restore(string $emailId, string $tenantId): void
    {
        $email = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $email->update([
            'folder' => 'inbox',
            'deleted_at' => null,
        ]);
    }

    public function permanentDelete(string $emailId, string $tenantId): void
    {
        $email = Email::where('tenant_id', $tenantId)
            ->where('id', $emailId)
            ->firstOrFail();

        $directory = storage_path('app/emails/' . $tenantId);
        $attachments = $email->attachments;

        foreach ($attachments as $attachment) {
            $filePath = $directory . '/' . $attachment->file_name;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $attachment->delete();
        }

        $email->recipients()->delete();
        $email->labels()->detach();
        $email->forceDelete();
    }

    public function markAsSpam(string $emailId, string $tenantId): void
    {
        $this->moveToFolder($emailId, 'spam', $tenantId);
    }
}
