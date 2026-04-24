<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

interface MiddlewareInterface
{
    public function handle(): void;
}