<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

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
            redirect('/connexion');
        }

        if (headers_sent())
        {
            return;
        }

        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}