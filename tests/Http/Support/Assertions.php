<?php

declare(strict_types=1);

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

function assert_html(string $body): bool
{
    return str_contains(strtolower($body), '<html');
}

function assert_title(string $body): bool
{
    return preg_match('/<title\b[^>]*>.*?<\/title>/is', $body) === 1;
}

/**
 * @param list<string> $headers
 */
function assert_header(array $headers, string $needle): bool
{
    $needle = strtolower($needle);

    foreach ($headers as $header)
    {
        if (str_contains(strtolower($header), $needle))
        {
            return true;
        }
    }

    return false;
}