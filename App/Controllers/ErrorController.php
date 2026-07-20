<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Common\ServiceResult;

use Framework\Http\Request;
use Framework\Support\Logger;

final class ErrorController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP ERRORS
    |--------------------------------------------------------------------------
    */

    public function unauthorized(string $message = 'Non authentifié'): never
    {
        $this->error(401, '401', '401 | Non authentifié', $message);
    }

    public function forbidden(string $message = 'Accès interdit'): never
    {
        $this->error(403, '403', '403 | Accès interdit', $message);
    }

    public function notFound(string $message = 'Page introuvable'): never
    {
        $this->error(404, '404', '404 | Page introuvable', $message);
    }

    public function methodNotAllowed(string $message = 'Méthode non autorisée'): never
    {
        $this->error(405, '405', '405 | Méthode non autorisée', $message);
    }

    public function csrfExpired(string $message = 'Session expirée ou requête invalide.'): never
    {
        $this->error(419, '419', '419 | Session expirée', $message);
    }

    public function validationError(string $message = 'Erreur de validation'): never
    {
        $this->error(422, '422', '422 | Erreur de validation', $message);
    }

    public function serverError(string $message = 'Une erreur interne est survenue.'): never
    {
        $this->error(500, '500', '500 | Erreur serveur', $message, true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function error(
        int $status,
        string $view,
        string $title,
        string $message,
        bool $critical = false
    ): never
    {
        $context = ['uri' => $this->request->uri()];

        if ($critical)
        {
            Logger::error($title, $context);
        }
        else
        {
            Logger::warning($title, $context);
        }

        if ($this->expectsJson())
        {
            $this->jsonResult(ServiceResult::error(message: $message, status: $status));
        }

        $this->title = $title;

        $this->renderError($view, $status, ['message' => $message]);
    }
}