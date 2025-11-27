<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

function handleRequest($kernel, $method, $uri, $data = [], $headers = []) {
    $server = [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
    ];

    $content = null;
    if (!empty($server['CONTENT_TYPE']) && str_contains($server['CONTENT_TYPE'], 'application/json')) {
        $content = json_encode($data);
        $parameters = [];
    } else {
        $parameters = $data;
    }

    foreach ($headers as $k => $v) {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
        $server[$serverKey] = $v;
    }

    $request = Illuminate\Http\Request::create($uri, $method, $parameters, [], [], $server, $content);
    $response = $kernel->handle($request);
    $decoded = json_decode($response->getContent(), true);
    $kernel->terminate($request, $response);
    return [$response->getStatusCode(), $decoded ?: $response->getContent()];
}

// Login with seeded user
list($status, $body) = handleRequest($kernel, 'POST', '/api/login', ['email' => 'test@example.com', 'password' => '1234']);
if ($status !== 200) {
    echo "Login failed: $status\n";
    print_r($body);
    exit(1);
}
$token = $body['token'];
$headers = ['Authorization' => 'Bearer ' . $token];

echo "Login OK (token length: " . strlen($token) . ")\n\n";

// Test A: create without title
list($sA, $bA) = handleRequest($kernel, 'POST', '/api/tasks', ['priority' => 'Low'], $headers);
echo "Test A - create missing title -> status: $sA\n";
echo json_encode($bA) . "\n\n";

// Test B: create with invalid priority
list($sB, $bB) = handleRequest($kernel, 'POST', '/api/tasks', ['title' => 'Bad priority', 'priority' => 'Urgent'], $headers);
echo "Test B - invalid priority -> status: $sB\n";
echo json_encode($bB) . "\n\n";

// Test C: create valid task
list($sC, $bC) = handleRequest($kernel, 'POST', '/api/tasks', ['title' => 'Valid Task', 'priority' => 'Medium'], $headers);
echo "Test C - valid create -> status: $sC\n";
echo json_encode($bC) . "\n\n";
$taskId = $bC['data']['id'] ?? null;
if (! $taskId) { echo "Could not create task for further tests\n"; exit(1); }

// Test D: complete when Draft
list($sD, $bD) = handleRequest($kernel, 'POST', "/api/tasks/$taskId/complete", [], $headers);
echo "Test D - complete while Draft -> status: $sD\n";
echo json_encode($bD) . "\n\n";

// Test E: in-process
list($sE, $bE) = handleRequest($kernel, 'POST', "/api/tasks/$taskId/in-process", [], $headers);
echo "Test E - in-process -> status: $sE\n";
echo json_encode($bE) . "\n\n";

// Test F: update when In-Process
list($sF, $bF) = handleRequest($kernel, 'PUT', "/api/tasks/$taskId", ['title' => 'Should Fail'], $headers);
echo "Test F - update while In-Process -> status: $sF\n";
echo json_encode($bF) . "\n\n";

// Test G: complete when In-Process
list($sG, $bG) = handleRequest($kernel, 'POST', "/api/tasks/$taskId/complete", [], $headers);
echo "Test G - complete while In-Process -> status: $sG\n";
echo json_encode($bG) . "\n\n";

echo "Validation tests done.\n";
