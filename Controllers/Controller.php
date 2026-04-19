<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Session;

abstract class Controller
{
    /**
     * Template principal.
     */
    protected string $template = 'layouts/base';

    /**
     * Titre de la page.
     */
    protected string $title;

    /**
     * Chemin de base.
     */
    protected string $basePath;

    public function __construct()
    {
        $this->title = App::siteName();
        $this->basePath = App::basePath();
    }

    /**
     * Retourne le chemin complet d'une vue standard.
     */
    protected function viewPath(string $file): string
    {
        return ROOT . '/Views/' . $file . '.php';
    }

    /**
     * Retourne le chemin complet d'une vue d'erreur.
     */
    protected function errorViewPath(string $file): string
    {
        return ROOT . '/Views/errors/' . $file . '.php';
    }

    /**
     * Retourne le chemin complet du template.
     */
    protected function templatePath(): string
    {
        return ROOT . '/Views/' . $this->template . '.php';
    }

    /**
     * Affiche une vue standard.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $file, array $data = []): void
    {
        $viewPath = $this->viewPath($file);

        if (!is_file($viewPath))
        {
            $this->notFound('Vue introuvable : ' . $file);
            return;
        }

        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        ob_start();
        require $viewPath;
        $content = ob_get_clean() ?: '';

        $templatePath = $this->templatePath();

        if (!is_file($templatePath))
        {
            $this->serverError('Template introuvable : ' . $this->template);
            return;
        }

        require $templatePath;
    }

    /**
     * Affiche une vue partielle sans template.
     *
     * @param array<string, mixed> $data
     */
    protected function renderPartial(string $file, array $data = []): void
    {
        $viewPath = $this->viewPath($file);

        if (!is_file($viewPath))
        {
            $this->notFound('Vue partielle introuvable : ' . $file);
            return;
        }

        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        require $viewPath;
    }

    /**
     * Affiche une vue d'erreur.
     *
     * @param array<string, mixed> $data
     */
    protected function renderError(string $file, int $statusCode, array $data = []): void
    {
        http_response_code($statusCode);

        $viewPath = $this->errorViewPath($file);

        if (!is_file($viewPath))
        {
            exit('Vue erreur introuvable : ' . $file);
        }

        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        ob_start();
        require $viewPath;
        $content = ob_get_clean() ?: '';

        $templatePath = $this->templatePath();

        if (!is_file($templatePath))
        {
            exit('Template introuvable : ' . $this->template);
        }

        require $templatePath;
    }

    /**
     * Redirection simple.
     */
    protected function redirect(string $url): void
    {
        if (preg_match('#^https?://#i', $url) === 1)
        {
            header('Location: ' . $url);
            exit;
        }

        header('Location: ' . $this->basePath . ltrim($url, '/'));
        exit;
    }

    /**
     * Redirection avec erreur simple.
     */
    protected function redirectWithError(string $url, string $message, bool $withOld = true): void
    {
        if ($withOld)
        {
            Session::set('old', $_POST);
        }

        Session::set('error', $message);
        $this->redirect($url);
    }

    /**
     * Redirection avec erreurs de validation.
     *
     * @param array<string, mixed> $errors
     */
    protected function redirectWithValidationErrors(
        string $url,
        array $errors,
        string $message = 'Le formulaire contient des erreurs.'
    ): void
    {
        Session::set('errors', $errors);
        Session::set('old', $_POST);
        Session::set('error', $message);

        $this->redirect($url);
    }

    /**
     * Redirection avec succès.
     */
    protected function redirectWithSuccess(string $url, string $message): void
    {
        Session::set('success', $message);
        $this->redirect($url);
    }

    /**
     * Page 404.
     */
    public function notFound(string $message = 'Page introuvable'): void
    {
        $this->title = '404 | Page introuvable';
        $this->renderError('404', 404, ['message' => $message]);
        exit;
    }

    /**
     * Page 405.
     */
    public function methodNotAllowed(string $message = 'Méthode non autorisée'): void
    {
        $this->title = '405 | Méthode non autorisée';
        $this->renderError('405', 405, ['message' => $message]);
        exit;
    }

    /**
     * Page 500.
     */
    public function serverError(string $message = 'Erreur interne du serveur'): void
    {
        $this->title = '500 | Erreur serveur';
        $this->renderError('500', 500, ['message' => $message]);
        exit;
    }
}