<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Application\App;

final class AjaxOnlyMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (is_ajax()) {
            return;
        }

        $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

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