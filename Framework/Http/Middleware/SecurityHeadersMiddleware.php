<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Request;
use Framework\Security\ContentSecurityPolicy;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    // =========================================
    // MIDDLEWARE
    // =========================================

    public function handle(Request $request): void
    {
        if (headers_sent())
        {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Content-Security-Policy: ' . ContentSecurityPolicy::policy());

        if ($this->isHttps())
        {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    // =========================================
    // HTTPS
    // =========================================

    private function isHttps(): bool
    {
        $https = $_SERVER['HTTPS'] ?? null;

        if (is_string($https) && $https !== '' && strtolower($https) !== 'off')
        {
            return true;
        }

        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
        {
            return true;
        }

        return env_bool('TRUST_PROXY', false)
            && strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }
}