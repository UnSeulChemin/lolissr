<?php

declare(strict_types=1);

namespace Framework\Http;

use App\Controllers\ErrorController;

use ErrorException;
use Framework\Application\App;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\JsonResponseException;
use Framework\Support\Logger;
use Throwable;

final class ErrorHandler
{
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
        int $line,
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

            if (! $exception instanceof BaseHttpException)
            {
                Logger::exception($exception, ['type' => 'uncaught_exception']);

                self::render500(App::debug() ? $exception->getMessage() : 'Une erreur interne est survenue.');
            }

            self::logHttpException($exception);
            self::renderHttpException($exception);
        }
        catch (Throwable $fallbackException)
        {
            Logger::exception($fallbackException, ['type' => 'error_handler_failure']);

            Response::html('Critical framework error.', 500);
        }
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

        if (! in_array($error['type'], $fatalErrors, true))
        {
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

        self::render500(App::debug() ? $error['message'] : 'Une erreur interne est survenue.');
    }

    // =========================================
    // HTTP
    // =========================================

    private static function logHttpException(BaseHttpException $exception): void
    {
        Logger::warning(
            'HTTP Exception',
            [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => $exception->getData(),
            ],
        );
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
            default => 'Une erreur interne est survenue.',
        };
    }

    private static function renderHttpException(BaseHttpException $exception): never
    {
        $request = Request::capture();
        $controller = new ErrorController($request);
        $message = self::message($exception);

        if ($request->expectsJson())
        {
            foreach ($exception->getHeaders() as $header => $value)
            {
                header("{$header}: {$value}");
            }

            Response::json(
                [
                    'success' => false,
                    'message' => $message,
                    'data' => $exception->getData(),
                ],
                $exception->getStatusCode(),
            );
        }

        match ($exception->getStatusCode())
        {
            401 => $controller->unauthorized($message),
            403 => $controller->forbidden($message),
            404 => $controller->notFound($message),
            405 => $controller->methodNotAllowed($message),
            419 => $controller->csrfExpired($message),
            422 => $controller->validationError($message),
            default => $controller->serverError($message),
        };
    }

    private static function render500(string $message): never
    {
        $controller = new ErrorController(Request::capture());

        $controller->serverError($message);
    }
}
