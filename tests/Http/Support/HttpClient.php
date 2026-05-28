<?php

declare(strict_types=1);

function http_get(string $url): array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ]);

    $body = @file_get_contents(
        $url,
        false,
        $context,
    );

    $headers = $http_response_header ?? [];

    $status = 0;

    if (
        isset($headers[0])
        && preg_match('/\s(\d{3})\s/', $headers[0], $match)
    ) {
        $status = (int) $match[1];
    }

    return [
        'status' => $status,
        'body' => $body ?: '',
    ];
}