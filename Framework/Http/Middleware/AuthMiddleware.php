<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Auth\AuthenticationInterface;
use Framework\Exceptions\UnauthorizedException;
use Framework\Http\Request;

final readonly class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthenticationInterface $authentication
    ) {
    }

    // =========================================
    // MIDDLEWARE
    // =========================================

    public function handle(Request $request): void
    {
        if (! $this->authentication->check())
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