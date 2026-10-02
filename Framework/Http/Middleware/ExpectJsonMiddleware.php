<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Exceptions\JsonResponseException;
use Framework\Http\JsonResponse;
use Framework\Http\Request;

final class ExpectJsonMiddleware implements MiddlewareInterface
{
    // =================================================
    // FILTRE HTTP
    // =================================================

    public function handle(Request $request): void
    {
        if (! $request->expectsJson())
        {
            throw new JsonResponseException(
                JsonResponse::error(
                    'Requête JSON requise',
                    400
                )
            );
        }
    }
}