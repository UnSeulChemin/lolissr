<?php

declare(strict_types=1);

function assert_status(
    array $response,
    int $expected
): bool {
    return $response['status'] === $expected;
}