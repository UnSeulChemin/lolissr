<?php

declare(strict_types=1);

function assert_status(
    array $response,
    int $expected,
): bool {

    return ($response['status'] ?? 0)
        === $expected;
}

function assert_contains(
    string $body,
    string $needle,
): bool {

    return stripos(
        $body,
        $needle,
    ) !== false;
}

function assert_not_empty_body(
    string $body,
): bool {

    return trim($body) !== '';
}