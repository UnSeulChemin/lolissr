<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use ErrorException;
use Framework\Http\ErrorHandler;
use PHPUnit\Framework\TestCase;

final class ErrorHandlerTest extends TestCase
{
    public function testHandleError(): void
    {
        $this->expectException(
            ErrorException::class,
        );

        ErrorHandler::handleError(
            E_WARNING,
            'Test warning',
            __FILE__,
            __LINE__,
        );
    }
}