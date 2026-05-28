<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\ErrorController;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class ErrorControllerTest extends TestCase
{
    private ErrorController $controller;

    protected function setUp(): void
    {
        $this->controller =
            new ErrorController(
                new Request(),
            );
    }

    public function testMethodsExist(): void
    {
        $this->assertTrue(
            method_exists(
                $this->controller,
                'notFound',
            ),
        );

        $this->assertTrue(
            method_exists(
                $this->controller,
                'forbidden',
            ),
        );

        $this->assertTrue(
            method_exists(
                $this->controller,
                'unauthorized',
            ),
        );

        $this->assertTrue(
            method_exists(
                $this->controller,
                'serverError',
            ),
        );
    }

    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(
            ErrorController::class,
            $this->controller,
        );
    }
}