<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Exceptions\JsonResponseException;
use Framework\Http\Requests\Request;
use Framework\Http\Responses\JsonResponse;

final class ExpectJsonMiddleware implements MiddlewareInterface
{
    // =================================================
    // FILTRE HTTP
    // =================================================

    public function handle(Request $request): void
    {
        if (! $request->expectsJson())
        {
            throw new JsonResponseException(JsonResponse::error('Requête JSON requise', 400));
        }
    }
}