<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use ErrorException;
use Framework\Http\ErrorHandler;

final class ErrorHandlerTest
{
    public static function run(): array
    {
        return [

            self::testHandleError(),

        ];
    }

    private static function testHandleError(): array
    {
        $success = false;

        try {

            ErrorHandler::handleError(
                E_WARNING,
                'Test warning',
                __FILE__,
                __LINE__,
            );

        } catch (ErrorException) {

            $success = true;
        }

        return [
            'name' =>
                'ErrorHandler converts error to exception',

            'success' =>
                $success,
        ];
    }
}