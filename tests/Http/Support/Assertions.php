<?php

declare(strict_types=1);

use JsonException;

// =========================================
// CONTENU
// =========================================

function assert_contains(string $body, string $needle): bool
{
    return str_contains($body, $needle);
}

function assert_not_contains(string $body, string $needle): bool
{
    return ! str_contains($body, $needle);
}

function assert_not_empty_body(string $body): bool
{
    return trim($body) !== '';
}

// =========================================
// JSON
// =========================================

function assert_json(string $body): bool
{
    try
    {
        json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return true;
    }
    catch (JsonException)
    {
        return false;
    }
}

// =========================================
// HTML
// =========================================

function assert_html(string $body): bool
{
    return stripos($body, '<html') !== false;
}

function assert_title(string $body): bool
{
    return preg_match('/<title\b[^>]*>.*?<\/title>/is', $body) === 1;
}

// =========================================
// HEADERS
// =========================================

/**
 * @param list<string> $headers
 */
function assert_header(array $headers, string $needle): bool
{
    foreach ($headers as $header)
    {
        if (stripos($header, $needle) !== false)
        {
            return true;
        }
    }

    return false;
}