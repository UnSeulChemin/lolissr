<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Common\ServiceResult;
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

        $this->logWarning(
            '404 Not Found',
        );

        $this->respond(
            view: '404',
            status: 404,
            title: '404 | Page introuvable',
            message: $message,
        );
    }

    public function methodNotAllowed(
        string $message = 'Méthode non autorisée',
    ): never {

        $this->logWarning(
            '405 Method Not Allowed',
        );

        $this->respond(
            view: '405',
            status: 405,
            title: '405 | Méthode non autorisée',
            message: $message,
        );
    }

    public function csrfExpired(
        string $message = 'Session expirée ou requête invalide.',
    ): never {

        $this->logWarning(
            '419 CSRF Expired',
        );

        $this->respond(
            view: '419',
            status: 419,
            title: '419 | Session expirée',
            message: $message,
        );
    }

    public function forbidden(
        string $message = 'Accès interdit',
    ): never {

        $this->logWarning(
            '403 Forbidden',
        );

        $this->respond(
            view: '403',
            status: 403,
            title: '403 | Accès interdit',
            message: $message,
        );
    }

    public function unauthorized(
        string $message = 'Non authentifié',
    ): never {

        $this->logWarning(
            '401 Unauthorized',
        );

        $this->respond(
            view: '401',
            status: 401,
            title: '401 | Non authentifié',
            message: $message,
        );
    }

    public function serverError(
        string $message = 'Erreur interne du serveur',
    ): never {

        $this->logError(
            '500 Internal Server Error',
        );

        $this->respond(
            view: '500',
            status: 500,
            title: '500 | Erreur serveur',
            message: $message,
        );
    }

    private function respond(
        string $view,
        int $status,
        string $title,
        string $message,
    ): never {

        if ($this->isAjax()) {

            $result =
                ServiceResult::error(
                    message: $message,
                    status: $status,
                );

            $this->json(
                $result->toArray(),
                $result->status,
            );
        }

        $this->title = $title;

        $this->renderError(
            $view,
            $status,
            [
                'message' => $message,
            ],
        );
    }

    private function logWarning(
        string $message,
    ): void {

        Logger::warning(
            $message,
            [
                'uri' => $this->request->uri(),
            ],
        );
    }

    private function logError(
        string $message,
    ): void {

        Logger::error(
            $message,
            [
                'uri' => $this->request->uri(),
            ],
        );
    }
}