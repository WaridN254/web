<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\User::all() as $u) {
    $hash = (string) ($u->password_hash ?? '');
    if ($hash === '' || substr($hash, 0, 2) !== '$2') {
        $u->password_hash = Illuminate\Support\Facades\Hash::make('password');
        $u->save();
        echo $u->email . " => UPDATED\n";
    }
}
