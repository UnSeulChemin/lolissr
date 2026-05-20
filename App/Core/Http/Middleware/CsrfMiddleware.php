<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Application\App;
use App\Core\Http\Request;
use App\Core\Support\Session;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        if (
            App::isTesting()
            || env('APP_ENV') === 'testing'
        ) {
            return;
        }

        $sessionToken = Session::get(
            'csrf_token',
        );

        $postedToken = $request->input('csrf_token')
            ?? $request->header('X-CSRF-TOKEN');

        if (
            !is_string($sessionToken)
            || !is_string($postedToken)
            || !hash_equals($sessionToken, $postedToken)
        ) {
            json([
                'success' => false,
                'message' => 'Token CSRF invalide',
            ], 419);
        }
    }
}
