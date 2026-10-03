<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Request;
use Framework\Http\RequestContext;
use Framework\Security\ContentSecurityPolicy;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    // =================================================
    // FILTRE HTTP
    // =================================================

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

        if ($request->isHttps())
        {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
        }
    }
}
