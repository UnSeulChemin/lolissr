<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}
