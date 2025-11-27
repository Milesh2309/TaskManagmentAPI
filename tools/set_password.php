<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'test@example.com';
$new = $argv[2] ?? '1234';

$user = App\Models\User::where('email', $email)->first();
if (! $user) {
    echo json_encode(['ok' => false, 'message' => 'User not found', 'email' => $email]) . PHP_EOL;
    exit(1);
}

$user->password = bcrypt($new);
$user->save();

echo json_encode(['ok' => true, 'email' => $user->email, 'password_hash' => $user->password]) . PHP_EOL;
