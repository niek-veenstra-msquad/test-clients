<?php

declare(strict_types=1);

function env_or_default(string $name, string $default): string
{
    $value = getenv($name);

    return $value === false || $value === '' ? $default : $value;
}

function normalize_path(string $path): string
{
    return str_starts_with($path, '/') ? $path : '/' . $path;
}

$host = env_or_default('SERVER_HOST', '127.0.0.1');
$port = env_or_default('SERVER_PORT', '8080');
$path = normalize_path(env_or_default('SERVER_PATH', '/'));
$message = env_or_default('CLIENT_MESSAGE', 'ping');

if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
    fwrite(STDERR, "SERVER_HOST must be a valid IPv4 address: {$host}\n");
    exit(1);
}

$payload = json_encode(['message' => $message], JSON_THROW_ON_ERROR);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\nClient-Language: php\r\n",
        'content' => $payload,
        'ignore_errors' => true,
        'timeout' => 10,
    ],
]);

$responseBody = @file_get_contents("http://{$host}:{$port}{$path}", false, $context);

if ($responseBody === false) {
    $error = error_get_last();
    fwrite(STDERR, ($error['message'] ?? 'HTTP request failed') . "\n");
    exit(1);
}

$statusLine = $http_response_header[0] ?? 'HTTP/1.1 500 Internal Server Error';
preg_match('/\s(\d{3})\s/', $statusLine, $matches);
$status = isset($matches[1]) ? (int) $matches[1] : 500;

echo "Status: {$status}\n";

if ($responseBody !== '') {
    echo $responseBody . "\n";
}

if ($status >= 400) {
    exit(1);
}
