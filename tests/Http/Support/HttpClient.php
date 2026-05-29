<?php

declare(strict_types=1);

function http_request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
): array {

    $context =
        stream_context_create([

            'http' => [

                'method' => strtoupper(
                    $method,
                ),

                'ignore_errors' => true,

                'timeout' => 10,

                'content' =>
                    $body ?? '',

                'header' => implode(
                    "\r\n",
                    array_merge(
                        [
                            'User-Agent: LoliSSR-TestRunner',
                        ],
                        $headers,
                    ),
                ),
            ],
        ]);

    $responseBody =
        @file_get_contents(
            $url,
            false,
            $context,
        );

    $responseHeaders =
        $http_response_header
        ?? [];

    $status = 0;

    if (
        isset($responseHeaders[0])
        && preg_match(
            '/\s(\d{3})\s/',
            $responseHeaders[0],
            $matches,
        )
    ) {
        $status = (int) $matches[1];
    }

    return [

        'status' => $status,

        'body' =>
            is_string($responseBody)
                ? $responseBody
                : '',

        'headers' =>
            $responseHeaders,
    ];
}

function http_get(
    string $url,
    array $headers = [],
): array {

    return http_request(
        'GET',
        $url,
        $headers,
    );
}

function http_post(
    string $url,
    array $headers = [],
    ?string $body = null,
): array {

    return http_request(
        'POST',
        $url,
        $headers,
        $body,
    );
}