<?php

namespace App\Controllers;

class ErrorController extends Controller
{
    /**
     * Affiche une erreur 404.
     */
    public function notFound(string $message = 'Page introuvable'): void
    {
        $this->title = '404 | Page introuvable';
        $this->renderError('404', 404, ['message' => $message]);
    }

    /**
     * Affiche une erreur 405.
     */
    public function methodNotAllowed(string $message = 'Méthode non autorisée'): void
    {
        $this->title = '405 | Méthode non autorisée';
        $this->renderError('405', 405, ['message' => $message]);
    }

    /**
     * Affiche une erreur 500.
     */
    public function serverError(string $message = 'Erreur interne du serveur'): void
    {
        $this->title = '500 | Erreur serveur';
        $this->renderError('500', 500, ['message' => $message]);
    }
}