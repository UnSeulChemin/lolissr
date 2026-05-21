<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;
use Framework\Support\Logger;

final class ErrorController extends Controller
{
    public function __construct(
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function notFound(
        string $message = 'Page introuvable',
    ): never {
        Logger::warning(
            '404 Not Found',
            [
                'uri' => $this->request->uri(),
            ],
        );

        $this->renderErrorPage(
            view: '404',
            status: 404,
            title: '404 | Page introuvable',
            message: $message,
        );
    }

    public function methodNotAllowed(
        string $message = 'Méthode non autorisée',
    ): never {
        Logger::warning(
            '405 Method Not Allowed',
            [
                'method' => $this->request->method(),
                'uri' => $this->request->uri(),
            ],
        );

        $this->renderErrorPage(
            view: '405',
            status: 405,
            title: '405 | Méthode non autorisée',
            message: $message,
        );
    }

    public function renderCsrfExpiredPage(): never
    {
        Logger::warning(
            '419 CSRF expired',
            [
                'uri' => $this->request->uri(),
            ],
        );

        $this->renderErrorPage(
            view: '419',
            status: 419,
            title: '419 | Session expirée',
            message: 'Session expirée ou requête invalide.',
        );
    }

    public function serverError(
        string $message = 'Erreur interne du serveur',
    ): never {
        Logger::error(
            '500 Internal Server Error',
            [
                'uri' => $this->request->uri(),
            ],
        );

        $this->renderErrorPage(
            view: '500',
            status: 500,
            title: '500 | Erreur serveur',
            message: $message,
        );
    }

    private function renderErrorPage(
        string $view,
        int $status,
        string $title,
        string $message,
    ): never {
        $this->title = $title;

        $this->renderError(
            $view,
            $status,
            [
                'message' => $message,
            ],
        );
    }
}