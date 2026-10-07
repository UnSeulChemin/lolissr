<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use Framework\Application\Bootstrap;
use Framework\Http\Requests\Request;

Bootstrap::loadEnvOnly();
$limit = Request::MAX_JSON_BODY_BYTES;
foreach ([false, true] as $chunked)
{
    foreach ([$limit, $limit + 1, 1] as $size)
    {
        $body = $size === 1 ? '{' : '{"value":"' . str_repeat('a', $size - 12) . '"}';
        $received = [];
        $curl = curl_init('http://localhost' . rtrim(base_uri(), '/') . '/connexion');
        $headers = ['Content-Type: application/problem+json', 'Accept: application/json', 'Expect:'];
        if ($chunked) $headers[] = 'Transfer-Encoding: chunked';
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$received): int
            {
                if (str_contains($line, ':'))
                {
                    [$name, $value] = explode(':', $line, 2);
                    $received[strtolower(trim($name))] = trim($value);
                }
                return strlen($line);
            },
            CURLOPT_TIMEOUT => 15
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($response === false) throw new RuntimeException('HTTP fixture failed: ' . $error);
        foreach (['x-content-type-options' => 'nosniff', 'x-frame-options' => 'DENY', 'referrer-policy' => 'no-referrer',
            'permissions-policy' => 'camera=(), microphone=(), geolocation=()'] as $name => $value)
            if (($received[$name] ?? null) !== $value) throw new RuntimeException('Missing security header: ' . $name);
        if (!preg_match('/^[a-f0-9]{16}$/D', $received['x-request-id'] ?? '')
            || !str_contains($received['content-security-policy'] ?? '', "script-src 'self' 'nonce-"))
            throw new RuntimeException('Request context or full CSP missing on JSON response.');
        if ($size !== $limit && isset($received['set-cookie'])) throw new RuntimeException('Early JSON errors opened a session.');
        if ($size > $limit)
        {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if ($status !== 413 || ($decoded['success'] ?? null) !== false || ($decoded['message'] ?? '') !== 'Corps JSON trop volumineux.')
            {
                throw new RuntimeException('Oversized JSON was not rejected consistently: ' . $status);
            }
        }
        elseif ($size === 1)
        {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if ($status !== 400 || ($decoded['success'] ?? null) !== false)
                throw new RuntimeException('Malformed JSON was not rejected: ' . $status);
        }
        elseif ($status !== 419)
        {
            throw new RuntimeException('Exact-limit JSON did not reach CSRF validation: ' . $status);
        }
    }
}
echo "PASS: JSON 400/413/419, security headers and no early session, with and without Content-Length.\n";
