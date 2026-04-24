<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;

final class PostOnlyMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Request::isPost())
        {
            abort(405);
        }
    }
}