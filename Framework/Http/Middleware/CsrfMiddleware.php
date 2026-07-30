<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\CsrfException;
use Framework\Http\Request;
use Framework\Support\Session;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    // =========================================
    // MIDDLEWARE
    // =========================================

    public function handle(Request $request): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true))
        {
            return;
        }

        $sessionToken = Session::get('csrf_token');
        $requestToken = $this->resolveRequestToken($request);

        if (
            ! is_string($sessionToken)
            || $sessionToken === ''
            || $requestToken === null
            || ! hash_equals($sessionToken, $requestToken)
        )
        {
            throw new CsrfException();
        }
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private function resolveRequestToken(Request $request): ?string
    {
        $inputToken = $request->input('csrf_token');

        if (is_string($inputToken) && $inputToken !== '')
        {
            return $inputToken;
        }

        $headerToken = $request->header('X-CSRF-TOKEN');

        return is_string($headerToken) && $headerToken !== ''
            ? $headerToken
            : null;
    }
}