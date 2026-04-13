<?php

namespace App\Controllers;

use App\Core\Functions;

abstract class Controller
{
    /* Template principal */
    protected string $template = 'layouts/base';

    /* Titre de la page */
    protected string $title;

    /* Chemin de base */
    protected string $basePath;

    public function __construct()
    {
        $this->title = Functions::siteName();
        $this->basePath = Functions::basePath();
    }

    /* Affiche une vue standard */
    public function render(string $file, array $data = []): void
    {
        $viewPath = ROOT . '/Views/' . $file . '.php';

        if (!is_file($viewPath))
        {
            $this->notFound('Vue introuvable : ' . $file);
        }

        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $templatePath = ROOT . '/Views/' . $this->template . '.php';

        if (!is_file($templatePath))
        {
            $this->serverError('Template introuvable : ' . $this->template);
        }

        require $templatePath;
    }

    /* Affiche une vue d'erreur */
    protected function renderError(string $file, int $statusCode, array $data = []): void
    {
        http_response_code($statusCode);

        $viewPath = ROOT . '/Views/errors/' . $file . '.php';

        if (!is_file($viewPath))
        {
            exit('Vue erreur introuvable : ' . $file);
        }

        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $templatePath = ROOT . '/Views/' . $this->template . '.php';

        if (!is_file($templatePath))
        {
            exit('Template introuvable : ' . $this->template);
        }

        require $templatePath;
    }

    /* Page 404 */
    protected function notFound(string $message = 'Page introuvable'): void
    {
        $this->title = '404 | Page introuvable';
        $this->renderError('404', 404, ['message' => $message]);
        exit;
    }

    /* Page 405 */
    protected function methodNotAllowed(string $message = 'Méthode non autorisée'): void
    {
        $this->title = '405 | Méthode non autorisée';
        $this->renderError('405', 405, ['message' => $message]);
        exit;
    }

    /* Page 500 */
    protected function serverError(string $message = 'Erreur interne du serveur'): void
    {
        $this->title = '500 | Erreur serveur';
        $this->renderError('500', 500, ['message' => $message]);
        exit;
    }
}