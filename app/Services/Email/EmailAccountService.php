<?php

namespace App\Services\Email;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailAccountService
{
    public function createAccount(array $data, string $userId, string $tenantId): EmailAccount
    {
        $smtpPassword = $data['smtp_password'] ?? null;
        $imapPassword = $data['imap_password'] ?? null;

        $account = EmailAccount::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $data['name'],
            'email_address' => $data['email_address'],
            'from_name' => $data['from_name'] ?? $data['name'],
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => $data['smtp_port'] ?? 587,
            'smtp_encryption' => $data['smtp_encryption'] ?? 'tls',
            'smtp_username' => $data['smtp_username'] ?? $data['email_address'],
            'smtp_password' => $smtpPassword ? Crypt::encryptString($smtpPassword) : null,
            'imap_host' => $data['imap_host'] ?? null,
            'imap_port' => $data['imap_port'] ?? 993,
            'imap_encryption' => $data['imap_encryption'] ?? 'ssl',
            'imap_username' => $data['imap_username'] ?? $data['email_address'],
            'imap_password' => $imapPassword ? Crypt::encryptString($imapPassword) : null,
            'is_active' => $data['is_active'] ?? true,
            'connection_status' => 'disconnected',
            'signature' => $data['signature'] ?? null,
            'reply_to' => $data['reply_to'] ?? null,
        ]);

        $testResult = $this->testSmtpConnection($account);
        if ($testResult['success']) {
            $account->update(['connection_status' => 'connected']);
        }

        return $account;
    }

    public function updateAccount(EmailAccount $account, array $data): EmailAccount
    {
        $updateData = collect($data)->only([
            'name', 'email_address', 'from_name', 'smtp_host', 'smtp_port',
            'smtp_encryption', 'smtp_username', 'imap_host', 'imap_port',
            'imap_encryption', 'imap_username', 'is_active', 'signature',
            'reply_to',
        ])->toArray();

        if (isset($data['smtp_password']) && $data['smtp_password'] !== null) {
            $updateData['smtp_password'] = Crypt::encryptString($data['smtp_password']);
        }

        if (isset($data['imap_password']) && $data['imap_password'] !== null) {
            $updateData['imap_password'] = Crypt::encryptString($data['imap_password']);
        }

        $account->update($updateData);

        return $account->fresh();
    }

    public function testSmtpConnection(EmailAccount $account): array
    {
        try {
            $email = $account->email_address ?? $account->smtp_username;
            if (empty($email)) {
                return ['success' => false, 'message' => 'No email address or username to test with'];
            }

            $transport = $this->createSmtpTransport($account);

            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            $message = (new \Symfony\Component\Mime\Email())
                ->from($email)
                ->to($email)
                ->subject('Connection Test')
                ->text('This is a connection test from ' . ($account->display_name ?? $email));

            $mailer->send($message);

            return [
                'success' => true,
                'message' => 'SMTP connection successful',
            ];
        } catch (\Exception $e) {
            Log::error('SMTP connection test failed for account ' . $account->id, [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function testImapConnection(EmailAccount $account): array
    {
        try {
            if (!$account->imap_host) {
                return [
                    'success' => false,
                    'message' => 'IMAP host not configured',
                ];
            }

            if (!function_exists('imap_open')) {
                return [
                    'success' => false,
                    'message' => 'PHP IMAP extension not available. Install php-imap or enable it in php.ini.',
                ];
            }

            $hostname = $account->imap_host;
            $port = $account->imap_port;
            $username = $account->imap_username;
            $password = Crypt::decryptString($account->imap_password);

            $connectionString = '{' . $hostname . ':' . $port . '/imap/' . $account->imap_encryption . '}INBOX';

            $mailbox = @imap_open($connectionString, $username, $password);

            if ($mailbox === false) {
                $errors = imap_errors();
                return [
                    'success' => false,
                    'message' => 'IMAP connection failed: ' . ($errors ? implode('; ', $errors) : 'Unknown error'),
                ];
            }

            imap_close($mailbox);

            return [
                'success' => true,
                'message' => 'IMAP connection successful',
            ];
        } catch (\Exception $e) {
            Log::error('IMAP connection test failed for account ' . $account->id, [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'IMAP connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function connectAccount(EmailAccount $account): EmailAccount
    {
        $smtpResult = $this->testSmtpConnection($account);
        $imapResult = $this->testImapConnection($account);

        $status = 'disconnected';
        if ($smtpResult['success'] && $imapResult['success']) {
            $status = 'connected';
        } elseif ($smtpResult['success'] || $imapResult['success']) {
            $status = 'partial';
        }

        $account->update([
            'connection_status' => $status,
            'last_synced_at' => $status === 'connected' ? now() : $account->last_synced_at,
        ]);

        if ($status === 'connected') {
            $this->updateUnreadCount($account);
        }

        return $account->fresh();
    }

    public function disconnectAccount(EmailAccount $account): EmailAccount
    {
        $account->update([
            'connection_status' => 'disconnected',
            'last_synced_at' => null,
        ]);

        return $account->fresh();
    }

    public function getActiveAccount(string $tenantId): ?EmailAccount
    {
        return EmailAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('connection_status', 'connected')
            ->first();
    }

    public function updateUnreadCount(EmailAccount $account): void
    {
        try {
            if (!$account->imap_host) {
                return;
            }

            if (!function_exists('imap_open')) {
                return;
            }

            $hostname = $account->imap_host;
            $port = $account->imap_port;
            $username = $account->imap_username;
            $password = Crypt::decryptString($account->imap_password);

            $connectionString = '{' . $hostname . ':' . $port . '/imap/' . $account->imap_encryption . '}INBOX';

            $mailbox = @imap_open($connectionString, $username, $password);

            if ($mailbox === false) {
                return;
            }

            $unreadCount = 0;
            $headers = imap_search($mailbox, 'UNSEEN');
            if ($headers !== false) {
                $unreadCount = count($headers);
            }

            imap_close($mailbox);

            $account->update([
                'unread_count' => $unreadCount,
                'last_synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update unread count for account ' . $account->id, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function createSmtpTransport(EmailAccount $account): \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport
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

        return $transport;
    }
}
