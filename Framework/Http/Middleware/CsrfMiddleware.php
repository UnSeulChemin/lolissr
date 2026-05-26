<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Exceptions\CsrfException;
use Framework\Http\Request;
use Framework\Support\Session;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            return;
        }

        $sessionToken = Session::get(
            'csrf_token',
        );

        $postedToken = $request->input(
            'csrf_token',
        ) ?? $request->header(
            'X-CSRF-TOKEN',
        );

        $validToken =
            is_string($sessionToken)
            && $sessionToken !== ''
            && is_string($postedToken)
            && $postedToken !== ''
            && hash_equals(
                $sessionToken,
                $postedToken,
            );

        if ($validToken) {
            return;
        }

        throw new CsrfException();
    }
}