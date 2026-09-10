<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== user_branches ===" . PHP_EOL;
$rows = DB::table('user_branches')->get();
echo $rows->toJson() . PHP_EOL;

echo PHP_EOL . "=== branches ===" . PHP_EOL;
$rows = DB::table('branches')->select('id', 'name', 'is_active', 'is_default', 'tenant_id')->get();
echo $rows->toJson() . PHP_EOL;

echo PHP_EOL . "=== users (default_branch_id, tenant_id) ===" . PHP_EOL;
$rows = DB::table('users')->select('id', 'default_branch_id', 'tenant_id')->get();
echo $rows->toJson() . PHP_EOL;
