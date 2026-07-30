<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\MethodNotAllowedException;
use Framework\Http\Request;

final class PostOnlyMiddleware implements MiddlewareInterface
{
    // =========================================
    // MIDDLEWARE
    // =========================================

    public function handle(Request $request): void
    {
        if ($request->isPost())
        {
            return;
        }

        throw new MethodNotAllowedException(
            headers: [
                'Allow' => 'POST',
            ]
        );
    }
}