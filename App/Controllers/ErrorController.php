<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Support\Logger;

final class ErrorController extends Controller
{
    public function notFound(string $message = 'Page introuvable'): void
    {
        Logger::warning('404 Not Found', [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->title = '404 | Page introuvable';

        $this->renderError('404', 404, [
            'message' => $message,
        ]);
    }

    public function methodNotAllowed(string $message = 'Méthode non autorisée'): void
    {
        Logger::warning('405 Method Not Allowed', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->title = '405 | Méthode non autorisée';

        $this->renderError('405', 405, [
            'message' => $message,
        ]);
    }

    public function renderCsrfExpiredPage(): void
    {
        Logger::warning('419 CSRF expired', [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->title = '419 | Session expirée';

        $this->renderError('419', 419, [
            'message' => 'Session expirée ou requête invalide.',
        ]);
    }

    public function serverError(string $message = 'Erreur interne du serveur'): void
    {
        Logger::error('500 Internal Server Error', [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->title = '500 | Erreur serveur';

        $this->renderError('500', 500, [
            'message' => $message,
        ]);
    }
}