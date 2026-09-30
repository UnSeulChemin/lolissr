<?php

declare(strict_types=1);

$base = rtrim($argv[1] ?? 'http://localhost/lolissr', '/');
$manifest = require dirname(__DIR__) . '/Config/javascript.php';
$request = static function (string $path, array $headers = []) use ($base): array {
    $received = [];
    $curl = curl_init($base . '/' . $path);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$received): int {
            if (str_contains($line, ':'))
            {
                [$name, $value] = explode(':', $line, 2);
                $received[strtolower(trim($name))] = trim($value);
            }
            return strlen($line);
        },
    ]);
    if (curl_exec($curl) === false) throw new RuntimeException(curl_error($curl));
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return [$status, $received];
};
[$status, $headers] = $request($manifest['entry']);
if ($status !== 200 || ($headers['cache-control'] ?? '') !== 'public, max-age=31536000, immutable')
    throw new RuntimeException('Bundle cache header missing; enable mod_headers and restart Apache');
[$status, $headers] = $request($manifest['entry'], ['If-None-Match: ' . $headers['etag']]);
if ($status !== 304 || !str_contains($headers['cache-control'] ?? '', 'immutable'))
    throw new RuntimeException('Conditional response lost immutable caching');
[$status, $headers] = $request($manifest['preloads'][0]);
if ($status !== 200 || !str_contains($headers['cache-control'] ?? '', 'immutable'))
    throw new RuntimeException('Nested chunk not cacheable');
foreach (['js/app.js', 'css/app.css', 'js/dist/chunks/missing-NOTREAL0.js'] as $path)
{
    [$status, $headers] = $request($path);
    if (str_contains($headers['cache-control'] ?? '', 'immutable')) throw new RuntimeException('Cache policy leaked: ' . $path);
    if (str_contains($path, 'missing-') && $status !== 404) throw new RuntimeException('Missing chunk is not a 404');
}
echo "PASS: immutable entry/chunks, 304 responses, unchanged source policy and no immutable error responses.\n";
