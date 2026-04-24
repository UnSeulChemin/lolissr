<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Support\Logger;

final class ErrorController extends Controller
{
    /**
     * 404 — Page non trouvée
     */
    public function notFound(): void
    {
        Logger::warning('404 Not Found', [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->renderError('errors/404', 404, [
            'title' => '404 | Page introuvable',
            'message' => 'La page demandée est introuvable.',
        ]);
    }

    /**
     * 405 — Méthode non autorisée
     */
    public function methodNotAllowed(): void
    {
        Logger::warning('405 Method Not Allowed', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->renderError('errors/405', 405, [
            'title' => '405 | Méthode non autorisée',
            'message' => 'Méthode HTTP non autorisée.',
        ]);
    }

    /**
     * 419 — CSRF expiré
     */
    public function renderCsrfExpiredPage(): void
    {
        Logger::warning('419 CSRF expired', [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->renderError('errors/419', 419, [
            'title' => '419 | Session expirée',
            'message' => 'Session expirée ou requête invalide.',
        ]);
    }

    /**
     * 500 — Erreur serveur
     */
    public function serverError(): void
    {
        Logger::error('500 Internal Server Error', [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
        ]);

        $this->renderError('errors/500', 500, [
            'title' => '500 | Erreur serveur',
            'message' => 'Une erreur interne est survenue.',
        ]);
    }
}