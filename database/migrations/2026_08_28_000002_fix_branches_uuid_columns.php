<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE branches ALTER COLUMN tenant_id TYPE text USING tenant_id::text");
        DB::statement("ALTER TABLE branches ALTER COLUMN manager_id TYPE text USING manager_id::text");
    }

    public function down(): void
    {
        //
    }
};
