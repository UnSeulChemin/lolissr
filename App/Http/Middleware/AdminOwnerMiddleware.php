<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Auth\AuthService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Middleware\MiddlewareInterface;
use Framework\Http\Requests\Request;

final readonly class AdminOwnerMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthService $authentication)
    {}

    public function handle(Request $request): void
    {
        if ($this->authentication->user()?->id !== 1) throw new NotFoundException();
    }
}
