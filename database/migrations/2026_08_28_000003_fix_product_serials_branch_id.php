<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE product_serials ALTER COLUMN branch_id TYPE text USING branch_id::text");
    }

    public function down(): void
    {
        //
    }
};
