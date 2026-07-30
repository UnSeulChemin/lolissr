<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Application\App;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\JsonResponseException;
use Framework\Support\Logger;

use Closure;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    private const INTERNAL_ERROR_MESSAGE = 'Une erreur interne est survenue.';

    /**
     * @var list<int>
     */
    private const FATAL_ERRORS = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
        E_RECOVERABLE_ERROR
    ];

    /**
     * @var (Closure(int, string, Request): never)|null
     */
    private static ?Closure $renderer = null;

    private function __construct()
    {
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    /**
     * @param callable(int, string, Request): never $renderer
     */
    public static function setRenderer(callable $renderer): void
    {
        self::$renderer = Closure::fromCallable($renderer);
    }

    // =========================================
    // GESTION DES ERREURS
    // =========================================

    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        if ((error_reporting() & $severity) === 0)
        {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(Throwable $exception): never
    {
        try
        {
            if ($exception instanceof JsonResponseException)
            {
                $exception->response()->send();
            }

            if ($exception instanceof BaseHttpException)
            {
                self::logHttpException($exception);
                self::renderHttpException($exception);
            }

            Logger::exception(
                $exception,
                [
                    'type' => 'uncaught_exception'
                ]
            );

            self::renderError(
                500,
                App::debug()
                    ? $exception->getMessage()
                    : self::INTERNAL_ERROR_MESSAGE
            );
        }
        catch (Throwable $fallbackException)
        {
            self::handleFailure($fallbackException, $exception);
        }
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null || ! self::isFatalError($error['type']))
        {
            return;
        }

        try
        {
            Logger::error(
                'Fatal Error',
                [
                    'type' => 'fatal_error',
                    'severity' => $error['type'],
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ]
            );

            self::renderError(
                500,
                App::debug()
                    ? $error['message']
                    : self::INTERNAL_ERROR_MESSAGE
            );
        }
        catch (Throwable $exception)
        {
            self::handleFailure($exception);
        }
    }

    // =========================================
    // HTTP
    // =========================================

    private static function logHttpException(BaseHttpException $exception): void
    {
        $context = [
            'type' => 'http_exception',
            'status' => $exception->getStatusCode(),
            'message' => $exception->getMessage(),
            'data' => $exception->getData()
        ];

        match ($exception->getStatusCode())
        {
            404 => Logger::info('HTTP Exception', $context),

            401,
            403,
            405,
            419,
            422 => Logger::warning('HTTP Exception', $context),

            default => Logger::error('HTTP Exception', $context)
        };
    }

    private static function renderHttpException(BaseHttpException $exception): never
    {
        $request = Request::capture();
        $status = $exception->getStatusCode();
        $message = self::message($exception);

        self::sendHeaders($exception->getHeaders());

        if ($request->expectsJson())
        {
            Response::json(
                [
                    'success' => false,
                    'message' => $message,
                    'data' => $exception->getData()
                ],
                $status
            );
        }

        self::renderError($status, $message, $request);
    }

    private static function message(BaseHttpException $exception): string
    {
        if (App::debug())
        {
            return $exception->getMessage();
        }

        return match ($exception->getStatusCode())
        {
            401 => 'Non authentifié',
            403 => 'Accès interdit',
            404 => 'Page introuvable',
            405 => 'Méthode non autorisée',
            419 => 'Session expirée',
            422 => 'Erreur de validation',
            default => self::INTERNAL_ERROR_MESSAGE
        };
    }

    /**
     * @param array<string, string> $headers
     */
    private static function sendHeaders(array $headers): void
    {
        if (headers_sent())
        {
            return;
        }

        foreach ($headers as $name => $value)
        {
            header("{$name}: {$value}", true);
        }
    }

    // =========================================
    // RENDU
    // =========================================

    private static function renderError(
        int $status,
        string $message,
        ?Request $request = null
    ): never {
        $request ??= Request::capture();

        if (self::$renderer !== null)
        {
            (self::$renderer)($status, $message, $request);
        }

        self::renderPlainError($status, $message);
    }

    private static function renderPlainError(int $status, string $message): never
    {
        if (! headers_sent())
        {
            http_response_code($status);
            header('Content-Type: text/plain; charset=UTF-8', true);
        }

        echo $message;

        exit;
    }

    private static function handleFailure(
        Throwable $exception,
        ?Throwable $originalException = null
    ): never {
        try
        {
            Logger::exception(
                $exception,
                [
                    'type' => 'error_handler_failure',
                    'original_exception' => $originalException !== null
                        ? $originalException::class
                        : null,
                    'original_message' => $originalException?->getMessage()
                ]
            );
        }
        catch (Throwable)
        {
            // Le logger lui-même est indisponible.
        }

        self::renderPlainError(500, 'Critical framework error.');
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private static function isFatalError(int $type): bool
    {
        return in_array($type, self::FATAL_ERRORS, true);
    }
}