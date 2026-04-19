<?php

declare(strict_types=1);

use App\Controllers\TestRouterController;
use App\Core\App;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        TestRouterController::reset();
    }

    protected function tearDown(): void
    {
        TestRouterController::reset();
    }

    // ...
}