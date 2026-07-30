<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\UnauthorizedException;
use Framework\Http\Request;

final class AuthMiddleware implements MiddlewareInterface
{
    // =========================================
    // MIDDLEWARE
    // =========================================

    public function handle(Request $request): void
    {
        if (! is_logged())
        {
            throw new UnauthorizedException();
        }

        if (headers_sent())
        {
            return;
        }

        header('Cache-Control: private, no-store, max-age=0', true);
        header('Pragma: no-cache', true);
        header('Expires: 0', true);
    }
}