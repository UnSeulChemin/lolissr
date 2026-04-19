<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Core\Application\App;
use App\Core\Support\Logger;

class ErrorHandler
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
        $message =
            $exception->getMessage()
            . ' in '
            . $exception->getFile()
            . ' on line '
            . $exception->getLine();

        if ($exception instanceof HttpException)
        {
            Logger::warning(
                'HTTP Exception [' . $exception->getStatusCode() . '] : '
                . $message
            );

            if (App::debug())
            {
                http_response_code($exception->getStatusCode());

                echo '<pre>';
                echo 'HTTP Exception [' . $exception->getStatusCode() . '] : ' . $message . PHP_EOL;
                echo $exception->getTraceAsString();
                echo '</pre>';

                exit;
            }

            self::renderHttpErrorPage($exception);
        }

        Logger::error(
            'Uncaught Exception: '
            . $message
            . PHP_EOL
            . $exception->getTraceAsString()
        );

        if (App::debug())
        {
            http_response_code(500);

            echo '<pre>';
            echo 'Exception : ' . $message . PHP_EOL;
            echo $exception->getTraceAsString();
            echo '</pre>';

            exit;
        }

        self::renderServerErrorPage();
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
            echo 'Fatal Error : ' . $message;
            echo '</pre>';

            exit;
        }

        self::renderServerErrorPage();
    }

    /**
     * Affiche la bonne page HTTP via le Controller.
     */
    private static function renderHttpErrorPage(HttpException $exception): void
    {
        http_response_code($exception->getStatusCode());

        $controller = new class extends \App\Controllers\Controller
        {
            public function show404(string $message): void
            {
                $this->renderNotFoundPage($message);
            }

            public function show405(string $message): void
            {
                $this->renderMethodNotAllowedPage($message);
            }

            public function show500(string $message): void
            {
                $this->renderServerErrorPage($message);
            }
        };

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

        $controller = new class extends \App\Controllers\Controller
        {
            public function show500(string $message = 'Erreur interne du serveur'): void
            {
                $this->renderServerErrorPage($message);
            }
        };

        $controller->show500();
    }
}