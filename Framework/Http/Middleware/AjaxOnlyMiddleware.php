<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Application\App;
use Framework\Http\Request;

final class AjaxOnlyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if ($request->isAjax()) {
            return;
        }

        $userAgent = $request->userAgent();

        if (
            App::isTesting()
            && str_contains($userAgent, 'LoliSSR-TestRunner')
        ) {
            return;
        }

        json([
            'success' => false,
            'message' => 'Requête AJAX requise',
        ], 400);
    }
}
