<?php

declare(strict_types=1);

function assert_status(
    array $response,
    int $expected,
): bool {
    return (int) ($response['status'] ?? 0)
        === $expected;
}

function assert_contains(
    string $body,
    string $needle,
): bool {
    return str_contains(
        $body,
        $needle,
    );
}

function assert_not_contains(
    string $body,
    string $needle,
): bool {
    return !str_contains(
        $body,
        $needle,
    );
}

function assert_not_empty_body(
    string $body,
): bool {
    return trim($body) !== '';
}

function assert_json(
    string $body,
): bool {
    json_decode(
        $body,
        true,
    );

    return json_last_error()
        === JSON_ERROR_NONE;
}

function assert_html(
    string $body,
): bool {
    return str_contains(
        strtolower($body),
        '<html',
    );
}

function assert_title(
    string $body,
): bool {
    return preg_match(
        '/<title\b[^>]*>.*?<\/title>/is',
        $body,
    ) === 1;
}

function assert_header(
    array $headers,
    string $needle,
): bool {
    foreach ($headers as $header)
    {
        if (
            str_contains(
                strtolower($header),
                strtolower($needle),
            )
        ) {
            return true;
        }
    }

    return false;
}