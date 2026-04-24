<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

final class AjaxOnlyMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!is_ajax())
        {
            abort(400);
        }
    }
}