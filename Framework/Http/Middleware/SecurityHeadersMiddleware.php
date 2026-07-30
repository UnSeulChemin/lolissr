<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Request;
use Framework\Http\RequestContext;
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

        header('X-Request-ID: ' . RequestContext::requestId(), true);
        header('X-Content-Type-Options: nosniff', true);
        header('X-Frame-Options: DENY', true);
        header('Referrer-Policy: no-referrer', true);
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()', true);
        header('Content-Security-Policy: ' . ContentSecurityPolicy::policy(), true);

        if ($this->isHttps($request))
        {
            header(
                'Strict-Transport-Security: max-age=31536000; includeSubDomains',
                true
            );
        }
    }

    // =========================================
    // HTTPS
    // =========================================

    private function isHttps(Request $request): bool
    {
        $https = $request->server('HTTPS');

        if (is_string($https) && $https !== '' && strtolower($https) !== 'off')
        {
            return true;
        }

        if ((int) $request->server('SERVER_PORT', 0) === 443)
        {
            return true;
        }

        if (! env_bool('TRUST_PROXY', false))
        {
            return false;
        }

        $forwardedProto = $request->header('X-Forwarded-Proto');

        return is_string($forwardedProto)
            && strtolower(trim(explode(',', $forwardedProto)[0])) === 'https';
    }
}