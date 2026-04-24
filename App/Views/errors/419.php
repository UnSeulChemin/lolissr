<?php

declare(strict_types=1);

namespace App\Controllers;

class ErrorController extends Controller
{
    public function renderNotFoundPage(): void
    {
        $this->renderError('errors/404', 404, [
            'title' => '404 | Page introuvable',
            'message' => 'Page introuvable.',
        ]);
    }

    public function renderMethodNotAllowedPage(): void
    {
        $this->renderError('errors/405', 405, [
            'title' => '405 | Méthode non autorisée',
            'message' => 'La méthode utilisée n’est pas autorisée.',
        ]);
    }

    public function renderCsrfExpiredPage(): void
    {
        $this->renderError('errors/419', 419, [
            'title' => '419 | Session expirée',
            'message' => 'Session expirée ou requête invalide.',
        ]);
    }

    public function renderServerErrorPage(): void
    {
        $this->renderError('errors/500', 500, [
            'title' => '500 | Erreur serveur',
            'message' => 'Une erreur serveur est survenue.',
        ]);
    }
}