<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if (! $user) {
    echo json_encode(['email' => null, 'password_hash' => null]);
    exit(0);
}

echo json_encode(['email' => $user->email, 'password_hash' => $user->password]);
