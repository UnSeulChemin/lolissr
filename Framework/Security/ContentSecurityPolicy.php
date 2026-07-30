<?php

declare(strict_types=1);

namespace Framework\Security;

use Random\RandomException;

final class ContentSecurityPolicy
{
    private static ?string $nonce = null;

    private function __construct()
    {
    }

    // =========================================
    // NONCE
    // =========================================

    public static function nonce(): string
    {
        if (self::$nonce === null)
        {
            self::$nonce = self::generateNonce();
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

    public static function reset(): void
    {
        self::$nonce = null;
    }

    // =========================================
    // POLICY
    // =========================================

    public static function policy(): string
    {
        $nonce = self::nonce();

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
            "form-action 'self';"
        ]);
    }

    // =========================================
    // GÉNÉRATION
    // =========================================

    private static function generateNonce(): string
    {
        try
        {
            return base64_encode(random_bytes(18));
        }
        catch (RandomException $exception)
        {
            throw new RandomException(
                'Impossible de générer le nonce CSP.',
                previous: $exception
            );
        }
    }
}