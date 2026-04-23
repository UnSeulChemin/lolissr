<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Controllers\Controller;
use App\Core\Application\App;
use App\Core\Support\Logger;

final class ErrorHandler
{
    /**
     * Initialise le handler global.
     */
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Transforme les erreurs PHP en ErrorException.
     */
    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool
    {
        if (!(error_reporting() & $severity))
        {
            return false;
        }

        throw new \ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }

    /**
     * Gère toutes les exceptions.
     */
    public static function handleException(\Throwable $exception): void
    {
        $message = self::formatExceptionMessage($exception);

        /*
        |--------------------------------------------------
        | HTTP Exceptions (404, 405...)
        |--------------------------------------------------
        */

        if ($exception instanceof HttpException)
        {
            Logger::warning(
                'HTTP Exception [' . $exception->getStatusCode() . '] : '
                . $message
            );

            self::safeRenderHttpErrorPage($exception);
            exit;
        }

        /*
        |--------------------------------------------------
        | Autres exceptions (500)
        |--------------------------------------------------
        */

        Logger::error(
            'Uncaught Exception: '
            . $message
            . PHP_EOL
            . $exception->getTraceAsString()
        );

        if (App::debug())
        {
            self::renderDebugException($exception);
        }

        self::safeRenderServerErrorPage();
        exit;
    }

    /**
     * Capture les fatal errors.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null)
        {
            return;
        }

        $fatalTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
        ];

        if (!in_array($error['type'], $fatalTypes, true))
        {
            return;
        }

        $message =
            $error['message']
            . ' in '
            . $error['file']
            . ' on line '
            . $error['line'];

        Logger::error('Fatal Error: ' . $message);

        if (App::debug())
        {
            http_response_code(500);

            echo '<pre>';
            echo 'Fatal Error : ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            echo '</pre>';

            exit;
        }

        self::safeRenderServerErrorPage();
        exit;
    }

    /**
     * Formate le message d’exception.
     */
    private static function formatExceptionMessage(\Throwable $exception): string
    {
        return $exception->getMessage()
            . ' in '
            . $exception->getFile()
            . ' on line '
            . $exception->getLine();
    }

    /**
     * Affiche une exception en mode debug.
     */
    private static function renderDebugException(\Throwable $exception): void
    {
        http_response_code(500);

        echo '<pre>';
        echo 'Exception : '
            . htmlspecialchars(self::formatExceptionMessage($exception), ENT_QUOTES, 'UTF-8')
            . PHP_EOL;
        echo htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';

        exit;
    }

    /**
     * Affiche la bonne page HTTP via le Controller.
     */
    private static function renderHttpErrorPage(HttpException $exception): void
    {
        http_response_code($exception->getStatusCode());

        $controller = self::makeErrorController();
        $statusCode = $exception->getStatusCode();
        $message = $exception->getMessage();

        if ($statusCode === 404)
        {
            $controller->show404($message);
            return;
        }

        if ($statusCode === 405)
        {
            $controller->show405($message);
            return;
        }

        $controller->show500($message);
    }

    /**
     * Affiche la page 500 via le Controller.
     */
    private static function renderServerErrorPage(): void
    {
        http_response_code(500);

        $controller = self::makeErrorController();
        $controller->show500();
    }

    /**
     * Rend une page HTTP en sécurité.
     */
    private static function safeRenderHttpErrorPage(HttpException $exception): void
    {
        try
        {
            self::renderHttpErrorPage($exception);
        }
        catch (\Throwable $renderException)
        {
            Logger::error(
                'Error while rendering HTTP error page: '
                . self::formatExceptionMessage($renderException)
            );

            self::renderPlainFallback(
                $exception->getStatusCode(),
                $exception->getMessage()
            );
        }
    }

    /**
     * Rend la page 500 en sécurité.
     */
    private static function safeRenderServerErrorPage(): void
    {
        try
        {
            self::renderServerErrorPage();
        }
        catch (\Throwable $renderException)
        {
            Logger::error(
                'Error while rendering 500 page: '
                . self::formatExceptionMessage($renderException)
            );

            self::renderPlainFallback(
                500,
                'Erreur interne du serveur'
            );
        }
    }

    /**
     * Fallback minimal si même la page d’erreur échoue.
     */
    private static function renderPlainFallback(int $statusCode, string $message): void
    {
        http_response_code($statusCode);

        echo '<h1>' . $statusCode . '</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';

        exit;
    }

    /**
     * Retourne un mini controller pour afficher les pages d’erreur.
     */
    private static function makeErrorController(): object
    {
        return new class extends Controller
        {
            public function show404(string $message): void
            {
                $this->renderNotFoundPage($message);
            }

            public function show405(string $message): void
            {
                $this->renderMethodNotAllowedPage($message);
            }

            public function show500(string $message = 'Erreur interne du serveur'): void
            {
                $this->renderServerErrorPage($message);
            }
        };
    }
}