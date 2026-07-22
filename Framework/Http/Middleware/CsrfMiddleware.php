<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\CsrfException;
use Framework\Http\Request;
use Framework\Support\Session;

final class CsrfMiddleware implements MiddlewareInterface
{
    // =========================================
    // MIDDLEWARE
    // =========================================

    public function handle(Request $request): void
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true))
        {
            return;
        }

        $sessionToken = Session::get('csrf_token');
        $requestToken = $request->input('csrf_token')
            ?? $request->header('X-CSRF-TOKEN');

        if (
            ! is_string($sessionToken)
            || $sessionToken === ''
            || ! is_string($requestToken)
            || $requestToken === ''
            || ! hash_equals($sessionToken, $requestToken)
        )
        {
            throw new CsrfException();
        }
    }
}