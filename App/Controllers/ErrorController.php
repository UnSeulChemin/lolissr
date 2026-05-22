<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Support\Logger;

final class ErrorController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function notFound(string $message = 'Page introuvable'): never
    {
        $this->logWarning('404 Not Found');
        $this->respond('404', 404, '404 | Page introuvable', $message);
    }

    public function methodNotAllowed(string $message = 'Méthode non autorisée'): never
    {
        $this->logWarning('405 Method Not Allowed');
        $this->respond('405', 405, '405 | Méthode non autorisée', $message);
    }

    public function csrfExpired(string $message = 'Session expirée ou requête invalide.'): never
    {
        $this->logWarning('419 CSRF Expired');
        $this->respond('419', 419, '419 | Session expirée', $message);
    }

    public function forbidden(string $message = 'Accès interdit'): never
    {
        $this->logWarning('403 Forbidden');
        $this->respond('403', 403, '403 | Accès interdit', $message);
    }

    public function unauthorized(string $message = 'Non authentifié'): never
    {
        $this->logWarning('401 Unauthorized');
        $this->respond('401', 401, '401 | Non authentifié', $message);
    }

    public function serverError(string $message = 'Erreur interne du serveur'): never
    {
        $this->logError('500 Internal Server Error');
        $this->respond('500', 500, '500 | Erreur serveur', $message);
    }

    /**
     * Réponse uniforme : JSON si AJAX, HTML sinon
     */
    private function respond(string $view, int $status, string $title, string $message): never
    {
        $this->ajaxOrHtml(
            fn () => $this->jsonError($message, $status),
            fn () => $this->renderErrorPage($view, $status, $title, $message)
        );
    }

    private function renderErrorPage(string $view, int $status, string $title, string $message): never
    {
        $this->title = $title;
        $this->renderError($view, $status, ['message' => $message]);
    }

    private function jsonError(string $message, int $status = 400): never
    {
        $this->json(['success' => false, 'message' => $message], $status);
    }

    private function ajaxOrHtml(callable $ajax, callable $html): void
    {
        if ($this->isAjax()) {
            $ajax();
            return;
        }
        $html();
    }

    private function logWarning(string $msg): void
    {
        Logger::warning($msg, ['uri' => $this->request->uri()]);
    }

    private function logError(string $msg): void
    {
        Logger::error($msg, ['uri' => $this->request->uri()]);
    }
}