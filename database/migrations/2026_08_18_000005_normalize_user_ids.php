<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('users')) {
            return;
        }

        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $currentId = (string) $user->id;

            if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $currentId)) {
                continue;
            }

            $newId = (string) Str::uuid();

            DB::table('users')->where('id', $currentId)->update(['id' => $newId]);

            $this->updateUserReferences('sessions', 'user_id', $currentId, $newId);
            $this->updateUserReferences('transactions', 'user_id', $currentId, $newId);
            $this->updateUserReferences('transactions', 'cashier_id', $currentId, $newId);
            $this->updateUserReferences('stock_movements', 'user_id', $currentId, $newId);
            $this->updateUserReferences('stock_audits', 'user_id', $currentId, $newId);
        }
    }

    protected function updateUserReferences(string $table, string $column, string $oldId, string $newId): void
    {
        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        if (! DB::getSchemaBuilder()->hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->whereRaw('CAST("' . $column . '" AS text) = ?', [$oldId])
            ->update([$column => $newId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data repair migration and is intentionally non-reversible.
    }
};
