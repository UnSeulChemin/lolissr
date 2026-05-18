<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;

final class PostOnlyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            abort(405);
        }
    }
}