<?php
declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\JsonResponseException;
use Framework\Http\JsonResponse;
use Framework\Http\Request;

final class AjaxOnlyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if ($request->isAjax()) {
            return;
        }

        // Lance l’exception JSON compatible
        throw new JsonResponseException(
            new JsonResponse([
                'success' => false,
                'message' => 'Requête AJAX requise'
            ], 400)
        );
    }
}