<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}
