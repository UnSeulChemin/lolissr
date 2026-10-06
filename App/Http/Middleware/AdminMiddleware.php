<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Auth\AuthService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Middleware\MiddlewareInterface;
use Framework\Http\Requests\Request;

final readonly class AdminMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthService $authentication)
    {
    }

    public function handle(Request $request): void
    {
        $user = $this->authentication->user();
        if ($user === null || ! $user->is_admin)
        {
            throw new NotFoundException();
        }
    }
}
