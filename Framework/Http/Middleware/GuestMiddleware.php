<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Auth\AuthenticationInterface;
use Framework\Exceptions\AlreadyAuthenticatedException;
use Framework\Http\Request;

final readonly class GuestMiddleware implements MiddlewareInterface
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
        if ($this->authentication->check())
        {
            throw new AlreadyAuthenticatedException();
        }
    }
}