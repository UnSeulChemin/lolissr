<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Request::method() !== 'POST') {
            return;
        }

        $sessionToken = $_SESSION['csrf_token'] ?? null;

        $postedToken = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? null;

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