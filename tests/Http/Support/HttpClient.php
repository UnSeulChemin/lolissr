<?php

declare(strict_types=1);

function http_get(
    string $url,
    array $headers = [],
): array {

    $context =
        stream_context_create([

            'http' => [

                'method' => 'GET',

                'ignore_errors' => true,

                'timeout' => 10,

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

    $body =
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

        'body' => is_string($body)
            ? $body
            : '',

        'headers' => $responseHeaders,
    ];
}