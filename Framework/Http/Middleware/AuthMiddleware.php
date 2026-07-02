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
        if (is_logged())
        {
            return;
        }

        redirect('/connexion');
    }
}
