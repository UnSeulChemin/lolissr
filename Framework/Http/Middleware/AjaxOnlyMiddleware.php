<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\JsonResponseException;
use Framework\Http\JsonResponse;
use Framework\Http\Request;

final class AjaxOnlyMiddleware implements MiddlewareInterface
{
    public function handle(
        Request $request,
    ): void {
        if ($request->isAjax()) {
            return;
        }

        throw new JsonResponseException(
            JsonResponse::error(
                'Requête AJAX requise',
                400,
            ),
        );
    }
}