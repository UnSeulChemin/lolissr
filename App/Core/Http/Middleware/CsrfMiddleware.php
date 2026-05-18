<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Application\App;
use App\Core\Http\Request;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    public function handle(): void
    {
        if ($this->request->method() !== 'POST') {
            return;
        }

        if (
            App::isTesting()
            || env('APP_ENV') === 'testing'
        ) {
            return;
        }

        $sessionToken = $_SESSION['csrf_token'] ?? null;

        $postedToken = $this->request->input('csrf_token')
            ?? $this->request->header('X-CSRF-TOKEN');

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