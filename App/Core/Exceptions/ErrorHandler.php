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
        if (!(error_reporting() & $severity))
        {
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

    public static function handleException(Throwable $exception): void
    {
        if ($exception instanceof HttpException)
        {
            Logger::warning('HTTP Exception', [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            self::safeRenderHttpErrorPage($exception);
            exit;
        }

        Logger::exception($exception, [
            'type' => 'uncaught_exception',
        ]);

        if (App::debug())
        {
            self::renderDebugException($exception);
        }

        self::safeRenderServerErrorPage();
        exit;
    }

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

        Logger::error('Fatal Error', [
            'type' => $error['type'],
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
        ]);

        $message =
            $error['message']
            . ' in '
            . $error['file']
            . ' on line '
            . $error['line'];

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

    private static function formatExceptionMessage(Throwable $exception): string
    {
        return $exception->getMessage()
            . ' in '
            . $exception->getFile()
            . ' on line '
            . $exception->getLine();
    }

    private static function renderDebugException(Throwable $exception): void
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

    private static function renderServerErrorPage(): void
    {
        http_response_code(500);

        $controller = self::makeErrorController();
        $controller->show500();
    }

    private static function safeRenderHttpErrorPage(HttpException $exception): void
    {
        try
        {
            self::renderHttpErrorPage($exception);
        }
        catch (Throwable $renderException)
        {
            Logger::exception($renderException, [
                'type' => 'render_http_error_page_failed',
            ]);

            self::renderPlainFallback(
                $exception->getStatusCode(),
                $exception->getMessage()
            );
        }
    }

    private static function safeRenderServerErrorPage(): void
    {
        try
        {
            self::renderServerErrorPage();
        }
        catch (Throwable $renderException)
        {
            Logger::exception($renderException, [
                'type' => 'render_500_page_failed',
            ]);

            self::renderPlainFallback(
                500,
                'Erreur interne du serveur'
            );
        }
    }

    private static function renderPlainFallback(int $statusCode, string $message): void
    {
        http_response_code($statusCode);

        echo '<h1>' . $statusCode . '</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';

        exit;
    }

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