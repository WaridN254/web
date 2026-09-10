<?php

namespace App\Console\Commands;

use App\Jobs\SyncEmailAccountJob;
use App\Models\EmailAccount;
use Illuminate\Console\Command;

class SyncEmailAccounts extends Command
{
    protected $signature = 'email:sync';
    protected $description = 'Sync email accounts that need synchronization';

    public function handle(): int
    {
        $accounts = EmailAccount::where('is_active', true)
            ->where('sync_enabled', true)
            ->where('connection_status', 'connected')
            ->where(function ($q) {
                $q->whereNull('last_synced_at')
                  ->orWhereRaw('last_synced_at < NOW() - (sync_interval_minutes || \' minutes\')::interval');
            })
            ->get();

        $this->info("Found {$accounts->count()} accounts to sync.");

        foreach ($accounts as $account) {
            SyncEmailAccountJob::dispatch($account);
            $this->line("  Dispatched sync for: {$account->email_address}");
        }

        return self::SUCCESS;
    }
}
