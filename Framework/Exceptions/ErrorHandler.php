<?php

declare(strict_types=1);

namespace Framework\Exceptions;

use App\Controllers\ErrorController;
use ErrorException;
use Framework\Application\App;
use Framework\Http\Request;
use Framework\Support\Logger;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler(
            [self::class, 'handleError'],
        );

        set_exception_handler(
            [self::class, 'handleException'],
        );

        register_shutdown_function(
            [self::class, 'handleShutdown'],
        );
    }

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line,
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
            $line,
        );
    }

    public static function handleException(
        Throwable $exception,
    ): never {
        try {

            /*
            |--------------------------------------------------------------------------
            | JSON RESPONSE EXCEPTION
            |--------------------------------------------------------------------------
            */

            if (
                $exception instanceof JsonResponseException
            ) {
                $exception
                    ->response()
                    ->send();
            }

            /*
            |--------------------------------------------------------------------------
            | HTTP EXCEPTIONS
            |--------------------------------------------------------------------------
            */

            if (
                $exception instanceof HttpException
            ) {
                Logger::warning(
                    'HTTP Exception',
                    [
                        'status' => $exception->getStatusCode(),
                        'message' => $exception->getMessage(),
                    ],
                );

                self::renderHttpException(
                    $exception,
                );
            }

            /*
            |--------------------------------------------------------------------------
            | UNCAUGHT EXCEPTION
            |--------------------------------------------------------------------------
            */

            Logger::exception(
                $exception,
                [
                    'type' => 'uncaught_exception',
                ],
            );

            if (App::debug()) {
                self::renderDebug(
                    $exception,
                );
            }

            self::render500();

        } catch (Throwable $fallbackException) {

            Logger::exception(
                $fallbackException,
                [
                    'type' => 'error_handler_failure',
                ],
            );

            http_response_code(500);

            exit('Critical framework error.');
        }
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
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
                true,
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
            ],
        );

        if (App::debug()) {
            self::renderFatalDebug(
                $error,
            );
        }

        self::render500();
    }

    private static function controller(): ErrorController
    {
        return new ErrorController(
            Request::capture(),
        );
    }

    private static function renderHttpException(
        HttpException $exception,
    ): never {
        $controller = self::controller();

        match ($exception->getStatusCode()) {

            404 => $controller->notFound(
                $exception->getMessage(),
            ),

            405 => $controller->methodNotAllowed(
                $exception->getMessage(),
            ),

            419 => $controller->renderCsrfExpiredPage(),

            default => $controller->serverError(
                $exception->getMessage(),
            ),
        };
    }

    private static function render500(): never
    {
        self::controller()
            ->serverError(
                'Erreur interne du serveur.',
            );
    }

    private static function renderDebug(
        Throwable $exception,
    ): never {
        http_response_code(500);

        echo '<pre>';

        echo htmlspecialchars(
            sprintf(
                "%s\n\n%s in %s on line %d",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
            ),
            ENT_QUOTES,
            'UTF-8',
        );

        echo PHP_EOL . PHP_EOL;

        echo htmlspecialchars(
            $exception->getTraceAsString(),
            ENT_QUOTES,
            'UTF-8',
        );

        echo '</pre>';

        exit;
    }

    /**
     * @param array{
     *     message: string,
     *     file: string,
     *     line: int
     * } $error
     */
    private static function renderFatalDebug(
        array $error,
    ): never {
        http_response_code(500);

        echo '<pre>';

        echo htmlspecialchars(
            sprintf(
                "Fatal error\n\n%s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line'],
            ),
            ENT_QUOTES,
            'UTF-8',
        );

        echo '</pre>';

        exit;
    }
}