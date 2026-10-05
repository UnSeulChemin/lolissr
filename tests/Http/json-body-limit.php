<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Application\Bootstrap;
use Framework\Http\Request;

Bootstrap::loadEnvOnly();
$limit = Request::MAX_JSON_BODY_BYTES;
foreach ([false, true] as $chunked)
{
    foreach ([$limit, $limit + 1] as $size)
    {
        $body = '{"value":"' . str_repeat('a', $size - 12) . '"}';
        $curl = curl_init('http://localhost' . rtrim(base_uri(), '/') . '/connexion');
        $headers = ['Content-Type: application/problem+json', 'Accept: application/json', 'Expect:'];
        if ($chunked) $headers[] = 'Transfer-Encoding: chunked';
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($response === false) throw new RuntimeException('HTTP fixture failed: ' . $error);
        if ($size > $limit)
        {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if ($status !== 413 || ($decoded['success'] ?? null) !== false || ($decoded['message'] ?? '') !== 'Corps JSON trop volumineux.')
            {
                throw new RuntimeException('Oversized JSON was not rejected consistently: ' . $status);
            }
        }
        elseif ($status !== 419)
        {
            throw new RuntimeException('Exact-limit JSON did not reach CSRF validation: ' . $status);
        }
    }
}
echo "PASS: exact JSON limit accepted, overflow rejected with JSON 413, including chunked requests without Content-Length.\n";
