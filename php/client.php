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
$socket = @stream_socket_client(
    "tcp://{$host}:{$port}",
    $errorCode,
    $errorMessage,
    10
);

if ($socket === false) {
    fwrite(STDERR, ($errorMessage !== '' ? $errorMessage : 'HTTP request failed') . "\n");
    exit(1);
}

stream_set_timeout($socket, 10);

$request = implode("\r\n", [
    "POST {$path} HTTP/1.1",
    "Host: {$host}:{$port}",
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload),
    'Connection: close',
    '',
    $payload,
]);

fwrite($socket, $request);

$response = stream_get_contents($socket);
$metadata = stream_get_meta_data($socket);
fclose($socket);

if ($response === false || ($metadata['timed_out'] ?? false)) {
    fwrite(STDERR, "HTTP request failed\n");
    exit(1);
}

[$rawHeaders, $responseBody] = array_pad(explode("\r\n\r\n", $response, 2), 2, '');
$headerLines = explode("\r\n", $rawHeaders);
$statusLine = $headerLines[0] ?? 'HTTP/1.1 500 Internal Server Error';
preg_match('/\s(\d{3})\s/', $statusLine, $matches);
$status = isset($matches[1]) ? (int) $matches[1] : 500;

echo "Status: {$status}\n";

if ($responseBody !== '') {
    echo $responseBody . "\n";
}

if ($status >= 400) {
    exit(1);
}
