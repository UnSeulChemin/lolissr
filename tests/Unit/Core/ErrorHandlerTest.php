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

            self::testHandleErrorThrowsException(),

            self::testHandleErrorMessage(),

            self::testHandleErrorSeverity(),

            self::testHandleErrorSuppressedReturnsFalse(),

        ];
    }

    private static function testHandleErrorThrowsException(): array
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

    private static function testHandleErrorMessage(): array
    {
        $success = false;

        try {

            ErrorHandler::handleError(
                E_NOTICE,
                'Expected message',
                __FILE__,
                __LINE__,
            );

        } catch (ErrorException $exception) {

            $success =
                $exception->getMessage()
                === 'Expected message';
        }

        return [
            'name' =>
                'ErrorHandler preserves message',

            'success' =>
                $success,
        ];
    }

    private static function testHandleErrorSeverity(): array
    {
        $success = false;

        try {

            ErrorHandler::handleError(
                E_USER_WARNING,
                'Severity test',
                __FILE__,
                __LINE__,
            );

        } catch (ErrorException $exception) {

            $success =
                $exception->getSeverity()
                === E_USER_WARNING;
        }

        return [
            'name' =>
                'ErrorHandler preserves severity',

            'success' =>
                $success,
        ];
    }

    private static function testHandleErrorSuppressedReturnsFalse(): array
    {
        $previous =
            error_reporting();

        error_reporting(0);

        $result =
            ErrorHandler::handleError(
                E_WARNING,
                'Ignored',
                __FILE__,
                __LINE__,
            );

        error_reporting(
            $previous,
        );

        return [
            'name' =>
                'ErrorHandler ignores suppressed errors',

            'success' =>
                $result === false,
        ];
    }
}