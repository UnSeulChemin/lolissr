<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Controllers\Controller;
use App\Core\Application\App;
use App\Core\Support\Logger;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler(
            [self::class, 'handleError']
        );

        set_exception_handler(
            [self::class, 'handleException']
        );

        register_shutdown_function(
            [self::class, 'handleShutdown']
        );
    }

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        if (
            (error_reporting() & $severity) === 0
        ) {
            return false;
        }

        throw new ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }

    public static function handleException(
        Throwable $exception
    ): never {
        if (
            $exception instanceof HttpException
        ) {
            Logger::warning(
                'HTTP Exception',
                [
                    'status' => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                ]
            );

            self::renderHttpException(
                $exception
            );
        }

        Logger::exception(
            $exception,
            [
                'type' => 'uncaught_exception',
            ]
        );

        if (App::debug())
        {
            self::renderDebug(
                $exception
            );
        }

        self::render500();
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null)
        {
            return;
        }

        $fatalErrors = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
        ];

        if (
            !in_array(
                $error['type'],
                $fatalErrors,
                true
            )
        ) {
            return;
        }

        Logger::error(
            'Fatal Error',
            [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
            ]
        );

        if (App::debug())
        {
            http_response_code(500);

            echo '<pre>';

            echo htmlspecialchars(
                $error['message']
                . ' in '
                . $error['file']
                . ' on line '
                . $error['line'],
                ENT_QUOTES,
                'UTF-8'
            );

            echo '</pre>';

            exit;
        }

        self::render500();
    }

    private static function renderHttpException(
        HttpException $exception
    ): never {
        $controller = new class extends Controller {};

        match ($exception->getStatusCode()) {

            404 => $controller->renderNotFoundPage(
                $exception->getMessage()
            ),

            405 => $controller->renderMethodNotAllowedPage(
                $exception->getMessage()
            ),

            default => $controller->renderServerErrorPage(
                $exception->getMessage()
            ),
        };
    }

    private static function render500(): never
    {
        $controller = new class extends Controller {};

        $controller->renderServerErrorPage(
            'Erreur interne du serveur.'
        );
    }

    private static function renderDebug(
        Throwable $exception
    ): never {
        http_response_code(500);

        echo '<pre>';

        echo htmlspecialchars(
            $exception->getMessage()
            . ' in '
            . $exception->getFile()
            . ' on line '
            . $exception->getLine(),
            ENT_QUOTES,
            'UTF-8'
        );

        echo PHP_EOL . PHP_EOL;

        echo htmlspecialchars(
            $exception->getTraceAsString(),
            ENT_QUOTES,
            'UTF-8'
        );

        echo '</pre>';

        exit;
    }
}