<?php

declare(strict_types=1);

final class ReportSanitizer
{
    private const BODY_LIMIT = 3000;

    private const BODY_PATTERNS = [
        '/(<meta[^>]+name=["\']csrf-token["\'][^>]+content=["\'])[^"\']+(["\'])/i',
        '/(name=["\']csrf_token["\'][^>]+value=["\'])[^"\']+(["\'])/i',
        '/(["\']csrf_token["\']\s*[:=]\s*["\'])[^"\']+(["\'])/i',
        '/(["\']password["\']\s*[:=]\s*["\'])[^"\']+(["\'])/i',
        '/(Set-Cookie:\s*[^=]+=)[^;\r\n]+/i',
        '/(Cookie:\s*[^=]+=)[^;\r\n]+/i'
    ];

    private const BODY_REPLACEMENTS = [
        '$1[REDACTED]$2',
        '$1[REDACTED]$2',
        '$1[REDACTED]$2',
        '$1[REDACTED]$2',
        '$1[REDACTED]',
        '$1[REDACTED]'
    ];

    private function __construct()
    {
    }

    // =========================================
    // CORPS
    // =========================================

    public static function body(string $body): string
    {
        $sanitizedBody = preg_replace(
            self::BODY_PATTERNS,
            self::BODY_REPLACEMENTS,
            $body
        );

        if (! is_string($sanitizedBody))
        {
            return '';
        }

        return mb_substr($sanitizedBody, 0, self::BODY_LIMIT);
    }

    // =========================================
    // HEADERS
    // =========================================

    /**
     * @param list<string> $headers
     */
    public static function headers(array $headers): string
    {
        $sanitizedHeaders = [];

        foreach ($headers as $header)
        {
            $sanitizedHeaders[] = self::header($header);
        }

        return implode("\n", $sanitizedHeaders);
    }

    private static function header(string $header): string
    {
        if (preg_match('/^(Set-Cookie|Cookie):/i', $header) !== 1)
        {
            return $header;
        }

        $sanitizedHeader = preg_replace(
            '/(:\s*[^=]+=)[^;\r\n]+/i',
            '$1[REDACTED]',
            $header
        );

        return is_string($sanitizedHeader)
            ? $sanitizedHeader
            : '[REDACTED]';
    }
}