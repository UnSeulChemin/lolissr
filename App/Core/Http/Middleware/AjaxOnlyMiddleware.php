<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Application\App;
use App\Core\Http\Request;

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