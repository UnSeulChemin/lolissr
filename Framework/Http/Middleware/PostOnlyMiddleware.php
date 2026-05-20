<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Request;

final class PostOnlyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            abort(405);
        }
    }
}
