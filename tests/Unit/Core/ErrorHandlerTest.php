<?php

declare(strict_types=1);

use App\Core\Exceptions\ErrorHandler;
use PHPUnit\Framework\TestCase;

final class ErrorHandlerTest extends TestCase
{
    public function testHandleErrorReturnsFalseWhenSeverityIsNotReported(): void
    {
        $previousLevel = error_reporting(0);

        try
        {
            $result = ErrorHandler::handleError(
                E_USER_NOTICE,
                'Message ignoré',
                __FILE__,
                10
            );

            $this->assertFalse($result);
        }
        finally
        {
            error_reporting($previousLevel);
        }
    }

    public function testHandleErrorThrowsErrorExceptionWhenSeverityIsReported(): void
    {
        $previousLevel = error_reporting(E_ALL);

        try
        {
            $this->expectException(\ErrorException::class);
            $this->expectExceptionMessage('Erreur test');

            ErrorHandler::handleError(
                E_USER_WARNING,
                'Erreur test',
                __FILE__,
                42
            );
        }
        finally
        {
            error_reporting($previousLevel);
        }
    }

    public function testHandleErrorThrowsErrorExceptionWithCorrectSeverityFileAndLine(): void
    {
        $previousLevel = error_reporting(E_ALL);

        try
        {
            try
            {
                ErrorHandler::handleError(
                    E_USER_ERROR,
                    'Boom',
                    '/tmp/fake-file.php',
                    123
                );

                $this->fail('Une ErrorException aurait dû être lancée.');
            }
            catch (\ErrorException $exception)
            {
                $this->assertSame('Boom', $exception->getMessage());
                $this->assertSame(E_USER_ERROR, $exception->getSeverity());
                $this->assertSame('/tmp/fake-file.php', $exception->getFile());
                $this->assertSame(123, $exception->getLine());
            }
        }
        finally
        {
            error_reporting($previousLevel);
        }
    }

    public function testRegisterSetsCustomHandlers(): void
    {
        $oldErrorHandler = set_error_handler(static function (): bool
        {
            return false;
        });

        restore_error_handler();

        $oldExceptionHandler = set_exception_handler(static function (): void
        {
        });

        restore_exception_handler();

        ErrorHandler::register();

        $currentErrorHandler = set_error_handler(static function (): bool
        {
            return false;
        });

        restore_error_handler();

        $currentExceptionHandler = set_exception_handler(static function (): void
        {
        });

        restore_exception_handler();

        $this->assertIsArray($currentErrorHandler);
        $this->assertSame(ErrorHandler::class, $currentErrorHandler[0]);
        $this->assertSame('handleError', $currentErrorHandler[1]);

        $this->assertIsArray($currentExceptionHandler);
        $this->assertSame(ErrorHandler::class, $currentExceptionHandler[0]);
        $this->assertSame('handleException', $currentExceptionHandler[1]);

        // 🔧 IMPORTANT : reset propre des handlers
        restore_error_handler();
        restore_exception_handler();
    }
}