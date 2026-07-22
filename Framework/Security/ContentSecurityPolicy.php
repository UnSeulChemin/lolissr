<?php

declare(strict_types=1);

namespace Framework\Security;

use RuntimeException;

final class ContentSecurityPolicy
{
    private static ?string $nonce = null;

    // =========================================
    // NONCE
    // =========================================

    public static function nonce(): string
    {
        if (self::$nonce === null)
        {
            self::$nonce = base64_encode(random_bytes(18));
        }

        return self::$nonce;
    }

    public static function escapedNonce(): string
    {
        return htmlspecialchars(
            self::nonce(),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }

    // =========================================
    // POLICY
    // =========================================

    public static function policy(): string
    {
        $nonce = self::nonce();

        if (str_contains($nonce, "\r") || str_contains($nonce, "\n"))
        {
            throw new RuntimeException('Nonce CSP invalide.');
        }

        return implode(' ', [
            "default-src 'self';",
            "script-src 'self' 'nonce-{$nonce}';",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
            "font-src 'self' https://fonts.gstatic.com;",
            "img-src 'self' data:;",
            "connect-src 'self';",
            "object-src 'none';",
            "base-uri 'self';",
            "frame-ancestors 'none';",
            "form-action 'self';",
        ]);
    }
}