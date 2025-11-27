<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

function handleRequest($kernel, $method, $uri, $data = [], $headers = []) {
    // Ensure JSON responses by setting appropriate server headers
    $server = [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
    ];

    // If content-type is JSON, pass raw JSON as the request content
    $content = null;
    if (!empty($server['CONTENT_TYPE']) && str_contains($server['CONTENT_TYPE'], 'application/json')) {
        $content = json_encode($data);
        $parameters = [];
    } else {
        $parameters = $data;
    }

    // Merge supplied headers into server vars as HTTP_* so auth middleware can read them
    foreach ($headers as $k => $v) {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
        $server[$serverKey] = $v;
    }

    $request = Illuminate\Http\Request::create($uri, $method, $parameters, [], [], $server, $content);

    $response = $kernel->handle($request);
    $content = $response->getContent();

    // Normalize JSON
    $decoded = json_decode($content, true);

    $kernel->terminate($request, $response);

    return [$response->getStatusCode(), $decoded ?: $content];
}

// 1) Login
// Use current seeded password (1234)
list($status, $body) = handleRequest($kernel, 'POST', '/api/login', ['email' => 'test@example.com', 'password' => '1234']);

echo "LOGIN STATUS: $status\n";
echo "LOGIN BODY: " . json_encode($body) . "\n\n";

if ($status !== 200 || empty($body['token'])) {
    echo "Login failed, aborting smoke test.\n";
    exit(1);
}

$token = $body['token'];

// 2) Create a task using the token
$headers = ['Authorization' => 'Bearer ' . $token];

// Resolve user from token and set the application auth user so internal requests are authenticated
try {
    $patModel = Laravel\Sanctum\PersonalAccessToken::findToken($token);
    if ($patModel) {
        $userModel = $patModel->tokenable;
        $app['auth']->setUser($userModel);
    }
} catch (Throwable $e) {
    // ignore if cannot resolve
}
$taskData = ['title' => 'Smoke Task', 'description' => 'Created during smoke test', 'priority' => 'High'];

list($tstatus, $tbody) = handleRequest($kernel, 'POST', '/api/tasks', $taskData, $headers);

echo "CREATE TASK STATUS: $tstatus\n";
echo "CREATE TASK BODY: " . json_encode($tbody) . "\n";

exit(0);
