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
        parent::__construct(
            $request,
        );
    }

    public function notFound(
        string $message = 'Page introuvable',
    ): never {

        $this->error(
            status: 404,
            view: '404',
            title: '404 | Page introuvable',
            message: $message,
        );
    }

    public function methodNotAllowed(
        string $message = 'Méthode non autorisée',
    ): never {

        $this->error(
            status: 405,
            view: '405',
            title: '405 | Méthode non autorisée',
            message: $message,
        );
    }

    public function csrfExpired(
        string $message = 'Session expirée ou requête invalide.',
    ): never {

        $this->error(
            status: 419,
            view: '419',
            title: '419 | Session expirée',
            message: $message,
        );
    }

    public function forbidden(
        string $message = 'Accès interdit',
    ): never {

        $this->error(
            status: 403,
            view: '403',
            title: '403 | Accès interdit',
            message: $message,
        );
    }

    public function unauthorized(
        string $message = 'Non authentifié',
    ): never {

        $this->error(
            status: 401,
            view: '401',
            title: '401 | Non authentifié',
            message: $message,
        );
    }

    public function serverError(
        string $message = 'Erreur interne du serveur',
    ): never {

        $this->error(
            status: 500,
            view: '500',
            title: '500 | Erreur serveur',
            message: $message,
            critical: true,
        );
    }

    private function error(
        int $status,
        string $view,
        string $title,
        string $message,
        bool $critical = false,
    ): never {

        $context = [
            'uri' =>
                $this->request->uri(),
        ];

        if ($critical) {

            Logger::error(
                $title,
                $context,
            );

        } else {

            Logger::warning(
                $title,
                $context,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON / AJAX
        |--------------------------------------------------------------------------
        */

        if (
            $this->expectsJson()
        ) {

            $this->jsonResult(
                ServiceResult::error(
                    message: $message,
                    status: $status,
                ),
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HTML
        |--------------------------------------------------------------------------
        */

        $this->title =
            $title;

        $this->renderError(
            $view,
            $status,
            [
                'message' =>
                    $message,
            ],
        );
    }
}