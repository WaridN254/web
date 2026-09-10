<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Concerns\HasPermission;
use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailLabel;
use App\Models\EmailRecipient;
use App\Models\EmailTemplate;
use App\Models\EmailThread;
use App\Services\Email\EmailAccountService;
use App\Services\Email\EmailFolderService;
use App\Services\Email\EmailLabelService;
use App\Services\Email\EmailSearchService;
use App\Services\Email\EmailSendService;
use App\Jobs\SyncEmailAccountJob;
use App\Jobs\SendEmailJob;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailPage extends Page
{
    use HasPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 1;


    public static function getNavigationGroup(): ?string
    {
        return __('navigation.communication');
    }



    public static function getNavigationLabel(): string
    {
        return __('navigation.email');
    }


    protected static ?string $title = 'Email';

    protected string $view = 'filament.tenant.pages.email-page';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('can_view_email') ?? false;
    }

    // Navigation state
    public string $currentFolder = 'inbox';
    public ?string $currentAccountId = null;

    // Email list
    public array $emails = [];
    public int $emailPage = 1;
    public int $emailTotalPages = 1;
    public int $emailTotalCount = 0;

    // Selected email / thread
    public ?string $selectedEmailId = null;
    public ?array $selectedEmail = null;
    public array $threadEmails = [];

    // Compose
    public bool $showCompose = false;
    public string $composeMode = 'new'; // new, reply, replyAll, forward, forwardAsAttachment, draft
    public ?string $composeDraftId = null;
    public ?string $composeReplyToId = null;
    public string $composeTo = '';
    public string $composeCc = '';
    public string $composeBcc = '';
    public string $composeSubject = '';
    public string $composeBody = '';
    public array $composeAttachments = [];
    public bool $composeShowCc = false;
    public bool $composeShowBcc = false;
    public ?string $composeUploadedFileName = null;
    public ?string $composeUploadedFilePath = null;

    public string $composeSendResult = '';
    public bool $composeSendSuccess = false;

    // Account settings
    public bool $showAccountSettings = false;
    public array $accounts = [];

    // Account form
    public ?string $editingAccountId = null;
    public string $accountEmail = '';
    public string $accountDisplayName = '';
    public string $accountSmtpHost = '';
    public int $accountSmtpPort = 587;
    public string $accountSmtpEncryption = 'tls';
    public string $accountSmtpUsername = '';
    public string $accountSmtpPassword = '';
    public string $accountImapHost = '';
    public int $accountImapPort = 993;
    public string $accountImapEncryption = 'ssl';
    public string $accountImapUsername = '';
    public string $accountImapPassword = '';
    public string $accountTestResult = '';

    // Labels
    public array $labels = [];
    public bool $showCreateLabel = false;
    public string $newLabelName = '';
    public string $newLabelColor = '#6366f1';

    // Search
    public string $searchQuery = '';
    public bool $isSearching = false;

    // Templates
    public array $templates = [];
    public bool $showTemplates = false;

    // Folder counts
    public array $folderCounts = [];

    // State
    public bool $showSidebar = true;

    protected function getTenantId(): ?string
    {
        return auth()->user()?->tenant_id;
    }

    public function mount(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $this->accounts = EmailAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->toArray();

        if (!empty($this->accounts)) {
            $this->currentAccountId = $this->accounts[0]['id'];
        }

        $this->loadLabels();
        $this->loadFolderCounts();
        $this->loadEmails();
    }

    // ─── FOLDER NAVIGATION ───────────────────────────────────

    public function setFolder(string $folder): void
    {
        $this->currentFolder = $folder;
        $this->selectedEmailId = null;
        $this->selectedEmail = null;
        $this->threadEmails = [];
        $this->emailPage = 1;
        $this->isSearching = false;
        $this->searchQuery = '';
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function switchAccount(?string $accountId): void
    {
        $this->currentAccountId = $accountId;
        $this->selectedEmailId = null;
        $this->selectedEmail = null;
        $this->threadEmails = [];
        $this->emailPage = 1;
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    // ─── LOAD EMAILS ─────────────────────────────────────────

    public function loadEmails(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId || !$this->currentAccountId) {
            $this->emails = [];
            return;
        }

        $query = Email::where('tenant_id', $tenantId)
            ->where('email_account_id', $this->currentAccountId);

        if ($this->isSearching && $this->searchQuery !== '') {
            $query = (new EmailSearchService())->searchQuery($query, $this->searchQuery);
        } else {
            $query = match ($this->currentFolder) {
                'inbox' => $query->where('folder', 'inbox'),
                'sent' => $query->where('folder', 'sent'),
                'drafts' => $query->where('is_draft', true),
                'starred' => $query->where('is_starred', true),
                'important' => $query->where('is_important', true),
                'spam' => $query->where('folder', 'spam'),
                'trash' => $query->where('folder', 'trash'),
                'archive' => $query->where('folder', 'archive'),
                default => $query->where('folder', $this->currentFolder),
            };
        }

        $this->emailTotalCount = $query->count();
        $perPage = 20;
        $this->emailTotalPages = max(1, (int) ceil($this->emailTotalCount / $perPage));

        $this->emails = $query->with(['recipients', 'labels', 'attachments'])
            ->orderByDesc('received_at')
            ->orderByDesc('created_at')
            ->offset(($this->emailPage - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'from_name' => $e->from_name,
                'from_address' => $e->from_address,
                'from_display' => $e->from_display,
                'subject' => $e->subject,
                'body_preview' => Str::limit(strip_tags($e->body_html ?? $e->body_text ?? ''), 120),
                'folder' => $e->folder,
                'is_read' => $e->is_read,
                'is_starred' => $e->is_starred,
                'is_important' => $e->is_important,
                'is_draft' => $e->is_draft,
                'has_attachments' => $e->has_attachments,
                'attachment_count' => $e->attachment_count,
                'received_at' => $e->received_at?->diffForHumans() ?? $e->created_at?->diffForHumans(),
                'received_at_raw' => $e->received_at?->toIso8601String() ?? $e->created_at?->toIso8601String(),
                'to_recipients' => $e->recipients->where('type', 'to')->pluck('email_address')->implode(', '),
                'labels' => $e->labels->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->toArray(),
            ])
            ->toArray();
    }

    // ─── SELECT EMAIL / THREAD ───────────────────────────────

    public function selectEmail(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $email = Email::where('tenant_id', $tenantId)
            ->with(['recipients', 'labels', 'attachments', 'thread.emails.recipients', 'thread.emails.attachments', 'thread.emails.labels'])
            ->find($emailId);

        if (!$email) return;

        $this->selectedEmailId = $emailId;

        if (!$email->is_read && $this->currentFolder !== 'sent' && !$email->is_draft) {
            $email->update(['is_read' => true]);
            Cache::forget("tenant:{$tenantId}:topbar_email");
            $this->updateUnreadCount();
        }

        $this->selectedEmail = [
            'id' => $email->id,
            'from_name' => $email->from_name,
            'from_address' => $email->from_address,
            'from_display' => $email->from_display,
            'subject' => $email->subject,
            'body_html' => $email->body_html,
            'body_text' => $email->body_text,
            'folder' => $email->folder,
            'is_read' => $email->is_read,
            'is_starred' => $email->is_starred,
            'is_important' => $email->is_important,
            'is_draft' => $email->is_draft,
            'has_attachments' => $email->has_attachments,
            'received_at' => $email->received_at?->format('d M Y, g:i A') ?? $email->created_at?->format('d M Y, g:i A'),
            'to_recipients' => $email->recipients->where('type', 'to')->map(fn ($r) => ['name' => $r->name, 'email' => $r->email_address])->toArray(),
            'cc_recipients' => $email->recipients->where('type', 'cc')->map(fn ($r) => ['name' => $r->name, 'email' => $r->email_address])->toArray(),
            'bcc_recipients' => $email->recipients->where('type', 'bcc')->map(fn ($r) => ['name' => $r->name, 'email' => $r->email_address])->toArray(),
            'attachments' => $email->attachments->map(fn ($a) => [
                'id' => $a->id,
                'file_name' => $a->file_name,
                'original_name' => $a->original_name,
                'mime_type' => $a->mime_type,
                'file_size' => $a->file_size,
                'formatted_size' => $a->formatted_size,
            ])->toArray(),
            'labels' => $email->labels->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->toArray(),
        ];

        if ($email->thread) {
            $this->threadEmails = $email->thread->emails()
                ->with(['recipients', 'attachments', 'labels'])
                ->orderBy('received_at', 'asc')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'from_name' => $e->from_name,
                    'from_address' => $e->from_address,
                    'from_display' => $e->from_display,
                    'subject' => $e->subject,
                    'body_html' => $e->body_html,
                    'body_text' => $e->body_text,
                    'is_read' => $e->is_read,
                    'is_draft' => $e->is_draft,
                    'received_at' => $e->received_at?->format('d M Y, g:i A') ?? $e->created_at?->format('d M Y, g:i A'),
                    'to_recipients' => $e->recipients->where('type', 'to')->pluck('email_address')->implode(', '),
                    'attachments' => $e->attachments->map(fn ($a) => [
                        'id' => $a->id,
                        'file_name' => $a->file_name,
                        'original_name' => $a->original_name,
                        'formatted_size' => $a->formatted_size ?? ($a->file_size . ' B'),
                    ])->toArray(),
                ])
                ->toArray();
        } else {
            $this->threadEmails = [$this->selectedEmail];
        }

        $this->loadEmails();
    }

    // ─── EMAIL ACTIONS ───────────────────────────────────────

    public function toggleStar(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->find($emailId);
        if ($email) {
            $email->update(['is_starred' => !$email->is_starred]);
            $this->loadEmails();
            if ($this->selectedEmailId === $emailId) {
                $this->selectedEmail['is_starred'] = $email->is_starred;
            }
        }
    }

    public function toggleImportant(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->find($emailId);
        if ($email) {
            $email->update(['is_important' => !$email->is_important]);
            $this->loadEmails();
            if ($this->selectedEmailId === $emailId) {
                $this->selectedEmail['is_important'] = $email->is_important;
            }
        }
    }

    public function markAsRead(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['is_read' => true]);
        Cache::forget("tenant:{$tenantId}:topbar_email");
        $this->updateUnreadCount();
        $this->loadEmails();
    }

    public function markAsUnread(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['is_read' => false]);
        Cache::forget("tenant:{$tenantId}:topbar_email");
        $this->updateUnreadCount();
        $this->loadEmails();
    }

    public function openEmail(string $emailId): void
    {
        $this->selectEmail($emailId);
    }

    public function moveToJunk(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['folder' => 'spam']);
        Cache::forget("tenant:{$tenantId}:topbar_email");
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function deleteEmail(string $emailId): void
    {
        $this->trashEmail($emailId);
    }

    public function archiveEmail(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['folder' => 'archive']);
        Cache::forget("tenant:{$tenantId}:topbar_email");
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function trashEmail(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['folder' => 'trash']);
        Cache::forget("tenant:{$tenantId}:topbar_email");
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function restoreEmail(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['folder' => 'inbox']);
        Cache::forget("tenant:{$tenantId}:topbar_email");
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function permanentDelete(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->find($emailId);
        if ($email) {
            $email->attachments()->delete();
            $email->recipients()->delete();
            $email->labels()->detach();
            $email->delete();
        }
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function moveToSpam(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['folder' => 'spam']);
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function moveOutOfSpam(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        Email::where('tenant_id', $tenantId)->where('id', $emailId)->update(['folder' => 'inbox']);
        $this->deselectEmail();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function deselectEmail(): void
    {
        $this->selectedEmailId = null;
        $this->selectedEmail = null;
        $this->threadEmails = [];
    }

    // ─── COMPOSE ─────────────────────────────────────────────

    public function openCompose(): void
    {
        $this->resetCompose();
        $this->composeMode = 'new';
        $this->showCompose = true;
    }

    public function openReply(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->with('recipients')->find($emailId);
        if (!$email) return;

        $this->resetCompose();
        $this->composeMode = 'reply';
        $this->composeReplyToId = $emailId;
        $this->composeTo = $email->from_address;
        $this->composeSubject = 'Re: ' . ltrim($email->subject, 'Re: ');
        $this->composeBody = $this->getQuotedBody($email);
        $this->showCompose = true;
    }

    public function openReplyAll(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->with('recipients')->find($emailId);
        if (!$email) return;

        $account = EmailAccount::find($this->currentAccountId);
        $myEmail = $account?->email_address ?? '';

        $toAddresses = $email->recipients->where('type', 'to')->pluck('email_address')->toArray();
        $ccAddresses = $email->recipients->where('type', 'cc')->pluck('email_address')->toArray();
        $allAddresses = array_merge($toAddresses, $ccAddresses);
        $allAddresses = array_filter($allAddresses, fn ($a) => strtolower($a) !== strtolower($myEmail));
        $allAddresses = array_unique($allAddresses);

        $this->resetCompose();
        $this->composeMode = 'replyAll';
        $this->composeReplyToId = $emailId;
        $this->composeTo = implode(', ', $allAddresses);
        $this->composeSubject = 'Re: ' . ltrim($email->subject, 'Re: ');
        $this->composeBody = $this->getQuotedBody($email);
        $this->showCompose = true;
    }

    public function openForward(string $emailId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->with('recipients')->find($emailId);
        if (!$email) return;

        $this->resetCompose();
        $this->composeMode = 'forward';
        $this->composeReplyToId = $emailId;
        $this->composeSubject = 'Fwd: ' . ltrim($email->subject, 'Fwd: ');
        $this->composeBody = $this->getForwardBody($email);
        $this->showCompose = true;
    }

    public function openDraft(string $draftId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)
            ->where('is_draft', true)
            ->with('recipients')
            ->find($draftId);
        if (!$email) return;

        $this->resetCompose();
        $this->composeMode = 'draft';
        $this->composeDraftId = $draftId;
        $this->composeTo = $email->recipients->where('type', 'to')->pluck('email_address')->implode(', ');
        $this->composeCc = $email->recipients->where('type', 'cc')->pluck('email_address')->implode(', ');
        $this->composeBcc = $email->recipients->where('type', 'bcc')->pluck('email_address')->implode(', ');
        $this->composeSubject = $email->subject;
        $this->composeBody = $this->stripQuotedFromDraft($email->body_html ?? $email->body_text ?? '');
        $this->composeShowCc = filled($this->composeCc);
        $this->composeShowBcc = filled($this->composeBcc);
        $this->showCompose = true;
    }

    public function closeCompose(): void
    {
        $this->showCompose = false;
        $this->resetCompose();
    }

    public function handleAttachmentUpload(): void
    {
        $uploadedFiles = $this->multipleFileUpload; // Will be set via Livewire file upload
        // This is handled via the blade file input + Livewire upload
    }

    public function uploadComposeFile(): void
    {
        // Called from blade after file selection
        $this->validate([
            'composeUploadedFileName' => 'nullable|string',
        ]);
    }

    public function removeComposeAttachment(int $index): void
    {
        if (isset($this->composeAttachments[$index])) {
            $file = $this->composeAttachments[$index];
            if (isset($file['temp_path']) && file_exists($file['temp_path'])) {
                @unlink($file['temp_path']);
            }
            array_splice($this->composeAttachments, $index, 1);
        }
    }

    public function addAttachmentFromUpload(string $fileName, string $tempPath, int $fileSize, string $mimeType): void
    {
        $this->composeAttachments[] = [
            'original_name' => $fileName,
            'temp_path' => $tempPath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
        ];
    }

    public function saveDraft(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId || !$this->currentAccountId) return;

        $data = $this->getComposeData();

        if ($this->composeDraftId) {
            $email = Email::where('tenant_id', $tenantId)->find($this->composeDraftId);
            if ($email) {
                $email->update([
                    'subject' => $data['subject'],
                    'body_html' => $data['body_html'],
                    'body_text' => $data['body_text'],
                ]);
                $email->recipients()->delete();
                $this->storeRecipients($email, $data);
            }
        } else {
            $emailId = Str::uuid();
            $email = Email::create([
                'id' => $emailId,
                'tenant_id' => $tenantId,
                'email_account_id' => $this->currentAccountId,
                'from_name' => auth()->user()->name,
                'from_address' => EmailAccount::find($this->currentAccountId)?->email_address ?? '',
                'subject' => $data['subject'],
                'body_html' => $data['body_html'],
                'body_text' => $data['body_text'],
                'folder' => 'drafts',
                'is_draft' => true,
                'sent_at' => null,
            ]);
            $this->storeRecipients($email, $data);
            $this->composeDraftId = $emailId;
        }

        $this->closeCompose();
        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function sendEmail(): void
    {
        $this->composeSendResult = '';
        $this->composeSendSuccess = false;

        $tenantId = $this->getTenantId();
        if (!$tenantId || !$this->currentAccountId) {
            $this->composeSendResult = 'No email account selected.';
            return;
        }

        $data = $this->getComposeData();

        if (empty($data['to'])) {
            $this->composeSendResult = 'Please enter at least one recipient email address.';
            return;
        }

        $toEmails = array_filter(array_map('trim', explode(',', $data['to'])));
        foreach ($toEmails as $addr) {
            if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $this->composeSendResult = "Invalid email address: {$addr}";
                return;
            }
        }

        $emailId = $this->composeDraftId ?? Str::uuid();

        $email = Email::updateOrCreate(
            ['id' => $emailId],
            [
                'tenant_id' => $tenantId,
                'email_account_id' => $this->currentAccountId,
                'from_name' => auth()->user()->name,
                'from_address' => EmailAccount::find($this->currentAccountId)?->email_address ?? '',
                'subject' => $data['subject'],
                'body_html' => $data['body_html'],
                'body_text' => $data['body_text'],
                'folder' => 'sent',
                'is_draft' => false,
                'is_read' => true,
            ]
        );

        if ($this->composeReplyToId) {
            $replyTo = Email::find($this->composeReplyToId);
            if ($replyTo) {
                if (!$replyTo->thread_id) {
                    $threadId = Str::uuid();
                    EmailThread::create([
                        'id' => $threadId,
                        'tenant_id' => $tenantId,
                        'email_account_id' => $this->currentAccountId,
                        'subject' => $replyTo->subject,
                        'last_message_at' => now(),
                        'message_count' => 1,
                        'folder' => 'inbox',
                    ]);
                    $replyTo->update(['thread_id' => $threadId]);
                    $email->update(['thread_id' => $threadId]);
                } else {
                    $email->update(['thread_id' => $replyTo->thread_id]);
                }
            }
        }

        $email->recipients()->delete();
        $this->storeRecipients($email, $data);

        // Store attachments
        $hasAttachments = false;
        foreach ($this->composeAttachments as $attachmentData) {
            if (isset($attachmentData['temp_path']) && file_exists($attachmentData['temp_path']) && isset($attachmentData['original_name'])) {
                $directory = 'emails/' . $tenantId;
                $filename = Str::uuid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $attachmentData['original_name']);
                $content = file_get_contents($attachmentData['temp_path']);

                Storage::disk('local')->put($directory . '/' . $filename, $content);

                EmailAttachment::create([
                    'id' => Str::uuid()->toString(),
                    'tenant_id' => $tenantId,
                    'email_id' => $email->id,
                    'original_name' => $attachmentData['original_name'],
                    'file_name' => $filename,
                    'storage_disk' => 'local',
                    'storage_path' => $directory . '/' . $filename,
                    'file_size' => $attachmentData['file_size'] ?? strlen($content),
                    'mime_type' => $attachmentData['mime_type'] ?? 'application/octet-stream',
                ]);

                @unlink($attachmentData['temp_path']);
                $hasAttachments = true;
            }
        }

        if ($hasAttachments) {
            $email->update([
                'has_attachments' => true,
                'attachment_count' => count($this->composeAttachments),
            ]);
        }

        $account = EmailAccount::find($this->currentAccountId);
        SendEmailJob::dispatch($email, $account);

        $this->composeSendResult = 'Email sent successfully!';
        $this->composeSendSuccess = true;
        $this->composeSending = false;

        $this->loadEmails();
        $this->loadFolderCounts();
    }

    public function sendTemplateEmail(string $templateId): void
    {
        $tenantId = $this->getTenantId();
        $template = EmailTemplate::where('tenant_id', $tenantId)->find($templateId);
        if (!$template) return;

        $this->composeSubject = $template->subject;
        $this->composeBody = $template->renderBody();
        $this->composeMode = 'new';
        $this->showCompose = true;
        $this->showTemplates = false;
    }

    // ─── LABELS ──────────────────────────────────────────────

    public function loadLabels(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $this->labels = EmailLabel::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function openCreateLabel(): void
    {
        $this->showCreateLabel = true;
        $this->newLabelName = '';
        $this->newLabelColor = '#6366f1';
    }

    public function createLabel(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId || empty($this->newLabelName)) return;

        EmailLabel::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => $this->newLabelName,
            'color' => $this->newLabelColor,
        ]);

        $this->showCreateLabel = false;
        $this->loadLabels();
    }

    public function deleteLabel(string $labelId): void
    {
        $tenantId = $this->getTenantId();
        $label = EmailLabel::where('tenant_id', $tenantId)->find($labelId);
        if ($label) {
            $label->emails()->detach();
            $label->delete();
            $this->loadLabels();
        }
    }

    public function toggleLabel(string $emailId, string $labelId): void
    {
        $tenantId = $this->getTenantId();
        $email = Email::where('tenant_id', $tenantId)->find($emailId);
        if (!$email) return;

        if ($email->labels->contains('id', $labelId)) {
            $email->labels()->detach($labelId);
        } else {
            $email->labels()->attach($labelId);
        }

        if ($this->selectedEmailId === $emailId) {
            $this->selectEmail($emailId);
        }
    }

    // ─── TEMPLATES ───────────────────────────────────────────

    public function loadTemplates(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $this->templates = EmailTemplate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function openTemplates(): void
    {
        $this->loadTemplates();
        $this->showTemplates = true;
    }

    public function closeTemplates(): void
    {
        $this->showTemplates = false;
    }

    // ─── ACCOUNT MANAGEMENT ──────────────────────────────────

    public function openAccountSettings(): void
    {
        $this->showAccountSettings = true;
        $this->resetAccountForm();
    }

    public function closeAccountSettings(): void
    {
        $this->showAccountSettings = false;
        $this->resetAccountForm();
    }

    public function editAccount(string $accountId = ''): void
    {
        if (empty($accountId)) {
            $this->resetAccountForm();
            return;
        }

        $tenantId = $this->getTenantId();
        $account = EmailAccount::where('tenant_id', $tenantId)->find($accountId);
        if (!$account) return;

        $this->editingAccountId = $accountId;
        $this->accountEmail = $account->email_address;
        $this->accountDisplayName = $account->display_name ?? '';
        $this->accountSmtpHost = $account->smtp_host ?? '';
        $this->accountSmtpPort = $account->smtp_port ?? 587;
        $this->accountSmtpEncryption = $account->smtp_encryption ?? 'tls';
        $this->accountSmtpUsername = $account->smtp_username ?? '';
        $this->accountSmtpPassword = '';
        $this->accountImapHost = $account->imap_host ?? '';
        $this->accountImapPort = $account->imap_port ?? 993;
        $this->accountImapEncryption = $account->imap_encryption ?? 'ssl';
        $this->accountImapUsername = $account->imap_username ?? '';
        $this->accountImapPassword = '';
    }

    public function saveAccount(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) return;

        $data = [
            'email_address' => $this->accountEmail,
            'display_name' => $this->accountDisplayName,
            'smtp_host' => $this->accountSmtpHost,
            'smtp_port' => $this->accountSmtpPort,
            'smtp_encryption' => $this->accountSmtpEncryption,
            'smtp_username' => $this->accountSmtpUsername,
            'imap_host' => $this->accountImapHost,
            'imap_port' => $this->accountImapPort,
            'imap_encryption' => $this->accountImapEncryption,
            'imap_username' => $this->accountImapUsername,
        ];

        if (filled($this->accountSmtpPassword)) {
            $data['smtp_password'] = Crypt::encryptString($this->accountSmtpPassword);
        }
        if (filled($this->accountImapPassword)) {
            $data['imap_password'] = Crypt::encryptString($this->accountImapPassword);
        }

        if ($this->editingAccountId) {
            EmailAccount::where('tenant_id', $tenantId)
                ->where('id', $this->editingAccountId)
                ->update($data);
        } else {
            $data['id'] = Str::uuid();
            $data['tenant_id'] = $tenantId;
            $data['user_id'] = auth()->id();
            $data['connection_status'] = 'disconnected';
            EmailAccount::create($data);
        }

        $this->accounts = EmailAccount::where('tenant_id', $tenantId)->where('is_active', true)->get()->toArray();
        $this->resetAccountForm();
    }

    public function testSmtpConnection(): void
    {
        $service = new EmailAccountService();
        $account = new EmailAccount();
        $account->smtp_host = $this->accountSmtpHost;
        $account->smtp_port = $this->accountSmtpPort;
        $account->smtp_encryption = $this->accountSmtpEncryption;
        $account->smtp_username = $this->accountSmtpUsername;
        $account->smtp_password = filled($this->accountSmtpPassword)
            ? Crypt::encryptString($this->accountSmtpPassword)
            : '';

        $result = $service->testSmtpConnection($account);
        $this->accountTestResult = $result['success'] ? '✅ ' . $result['message'] : '❌ ' . $result['message'];
    }

    public function testImapConnection(): void
    {
        $service = new EmailAccountService();
        $account = new EmailAccount();
        $account->imap_host = $this->accountImapHost;
        $account->imap_port = $this->accountImapPort;
        $account->imap_encryption = $this->accountImapEncryption;
        $account->imap_username = $this->accountImapUsername;
        $account->imap_password = filled($this->accountImapPassword)
            ? Crypt::encryptString($this->accountImapPassword)
            : '';

        $result = $service->testImapConnection($account);
        $this->accountTestResult = $result['success'] ? '✅ ' . $result['message'] : '❌ ' . $result['message'];
    }

    public function syncAccount(?string $accountId = null): void
    {
        $id = $accountId ?? $this->currentAccountId;
        if (!$id) return;
        $account = EmailAccount::find($id);
        if ($account) {
            SyncEmailAccountJob::dispatch($account);
        }
    }

    public function deleteAccount(string $accountId): void
    {
        $tenantId = $this->getTenantId();
        EmailAccount::where('tenant_id', $tenantId)->where('id', $accountId)->update(['is_active' => false]);
        $this->accounts = EmailAccount::where('tenant_id', $tenantId)->where('is_active', true)->get()->toArray();
        if ($this->currentAccountId === $accountId) {
            $this->currentAccountId = !empty($this->accounts) ? $this->accounts[0]['id'] : null;
        }
    }

    // ─── SEARCH ──────────────────────────────────────────────

    public function performSearch(): void
    {
        $this->isSearching = filled($this->searchQuery);
        $this->emailPage = 1;
        $this->loadEmails();
    }

    public function clearSearch(): void
    {
        $this->searchQuery = '';
        $this->isSearching = false;
        $this->emailPage = 1;
        $this->loadEmails();
    }

    // ─── PAGINATION ──────────────────────────────────────────

    public function nextPage(): void
    {
        if ($this->emailPage < $this->emailTotalPages) {
            $this->emailPage++;
            $this->loadEmails();
        }
    }

    public function prevPage(): void
    {
        if ($this->emailPage > 1) {
            $this->emailPage--;
            $this->loadEmails();
        }
    }

    // ─── HELPERS ─────────────────────────────────────────────

    private function resetCompose(): void
    {
        $this->composeMode = 'new';
        $this->composeDraftId = null;
        $this->composeReplyToId = null;
        $this->composeTo = '';
        $this->composeCc = '';
        $this->composeBcc = '';
        $this->composeSubject = '';
        $this->composeBody = '';
        $this->composeAttachments = [];
        $this->composeShowCc = false;
        $this->composeShowBcc = false;
        $this->composeSendResult = '';
        $this->composeSendSuccess = false;
    }

    private function resetAccountForm(): void
    {
        $this->editingAccountId = null;
        $this->accountEmail = '';
        $this->accountDisplayName = '';
        $this->accountSmtpHost = '';
        $this->accountSmtpPort = 587;
        $this->accountSmtpEncryption = 'tls';
        $this->accountSmtpUsername = '';
        $this->accountSmtpPassword = '';
        $this->accountImapHost = '';
        $this->accountImapPort = 993;
        $this->accountImapEncryption = 'ssl';
        $this->accountImapUsername = '';
        $this->accountImapPassword = '';
        $this->accountTestResult = '';
    }

    private function getComposeData(): array
    {
        $to = array_filter(array_map('trim', explode(',', $this->composeTo)));
        $cc = array_filter(array_map('trim', explode(',', $this->composeCc)));
        $bcc = array_filter(array_map('trim', explode(',', $this->composeBcc)));

        return [
            'to' => implode(', ', $to),
            'cc' => implode(', ', $cc),
            'bcc' => implode(', ', $bcc),
            'subject' => $this->composeSubject ?: '(No subject)',
            'body_html' => $this->composeBody,
            'body_text' => strip_tags($this->composeBody),
        ];
    }

    private function storeRecipients(Email $email, array $data): void
    {
        foreach (['to', 'cc', 'bcc'] as $type) {
            if (empty($data[$type])) continue;
            $addresses = array_filter(array_map('trim', explode(',', $data[$type])));
            foreach ($addresses as $addr) {
                if (empty($addr)) continue;
                EmailRecipient::create([
                    'id' => Str::uuid(),
                    'email_id' => $email->id,
                    'type' => $type,
                    'email_address' => $addr,
                    'name' => null,
                ]);
            }
        }
    }

    private function getQuotedBody(Email $email): string
    {
        $body = $email->body_html ?? $email->body_text ?? '';
        $date = $email->received_at?->format('d M Y, g:i A') ?? '';
        $from = $email->from_display ?? $email->from_address;

        return '<br><br><div style="border-left:3px solid #ccc;padding-left:12px;margin-top:16px;color:#666;">
            <p><strong>' . e($from) . '</strong> wrote on ' . e($date) . ':</p>
            <div>' . $body . '</div>
        </div>';
    }

    private function getForwardBody(Email $email): string
    {
        $body = $email->body_html ?? $email->body_text ?? '';
        $date = $email->received_at?->format('d M Y, g:i A') ?? '';
        $from = $email->from_display ?? $email->from_address;

        return '<br><br><div style="border-top:1px solid #ccc;padding-top:12px;margin-top:16px;">
            <p>---------- Forwarded message ---------</p>
            <p>From: <strong>' . e($from) . '</strong></p>
            <p>Date: ' . e($date) . '</p>
            <p>Subject: ' . e($email->subject) . '</p>
            <div>' . $body . '</div>
        </div>';
    }

    private function stripQuotedFromDraft(string $html): string
    {
        $pattern = '/<div style="border-left:3px solid #ccc.*?<\/div>\s*$/s';
        return preg_replace($pattern, '', $html) ?? $html;
    }

    private function updateUnreadCount(): void
    {
        if (!$this->currentAccountId) return;
        $tenantId = $this->getTenantId();
        $count = Email::where('tenant_id', $tenantId)
            ->where('email_account_id', $this->currentAccountId)
            ->where('folder', 'inbox')
            ->where('is_read', false)
            ->count();

        EmailAccount::where('id', $this->currentAccountId)->update(['unread_count' => $count]);
    }

    private function loadFolderCounts(): void
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId || !$this->currentAccountId) {
            $this->folderCounts = [];
            return;
        }

        $base = Email::where('tenant_id', $tenantId)->where('email_account_id', $this->currentAccountId);

        $this->folderCounts = [
            'inbox' => (clone $base)->where('folder', 'inbox')->count(),
            'starred' => (clone $base)->where('is_starred', true)->count(),
            'important' => (clone $base)->where('is_important', true)->count(),
            'sent' => (clone $base)->where('folder', 'sent')->count(),
            'drafts' => (clone $base)->where('is_draft', true)->count(),
            'spam' => (clone $base)->where('folder', 'spam')->count(),
            'trash' => (clone $base)->where('folder', 'trash')->count(),
            'archive' => (clone $base)->where('folder', 'archive')->count(),
        ];
    }

    public function getInitials(string $name): string
    {
        $parts = explode(' ', $name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return $initials ?: strtoupper(substr($name, 0, 1));
    }

    public function getAvatarColor(string $name): string
    {
        $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#0ea5e9'];
        $index = crc32($name) % count($colors);
        return $colors[$index];
    }
}
