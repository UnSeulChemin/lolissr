<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Application\App;
use Framework\Exceptions\MethodNotAllowedException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Support\Session;

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
    protected function viewPath(
        string $file,
    ): string {
        return view_path($file . '.php');
    }

    /**
     * Retourne le chemin complet d'une vue d'erreur.
     */
    protected function errorViewPath(
        string $file,
    ): string {
        $file = preg_replace(
            '#^errors/#',
            '',
            $file,
        );

        if ($file === null) {
            $file = '';
        }

        return view_path(
            'errors/' . $file . '.php',
        );
    }

    /**
     * Retourne le chemin complet du template.
     */
    protected function templatePath(): string
    {
        return view_path(
            $this->template . '.php',
        );
    }

    /**
     * @param array<string, mixed>|object $data
     */
    protected function render(
        string $file,
        array|object $data = [],
    ): never {
        $viewPath = $this->viewPath($file);

        if (!is_file($viewPath)) {
            throw new NotFoundException(
                'Vue introuvable : ' . $file,
            );
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        $view = $data;

        $title = $this->title;

        $basePath = $this->basePath;

        $currentPath = app(
            Request::class,
        )->path();

        ob_start();

        require $viewPath;

        $content = ob_get_clean();

        if ($content === false) {
            $content = '';
        }

        $templatePath = $this->templatePath();

        if (!is_file($templatePath)) {
            throw new \RuntimeException(
                'Template introuvable : '
                . $this->template,
            );
        }

        ob_start();

        require $templatePath;

        $html = ob_get_clean();

        if ($html === false) {
            $html = '';
        }

        Response::html($html);
    }

    /**
     * Affiche une vue partielle sans template.
     *
     * @param array<string, mixed> $data
     */
    protected function renderPartial(
        string $file,
        array $data = [],
    ): never {
        $viewPath = $this->viewPath($file);

        if (!is_file($viewPath)) {
            throw new NotFoundException(
                'Vue partielle introuvable : '
                . $file,
            );
        }

        $view = $data;

        $title = $this->title;

        $basePath = $this->basePath;

        $currentPath = app(
            Request::class,
        )->path();

        ob_start();

        require $viewPath;

        $html = ob_get_clean();

        if ($html === false) {
            $html = '';
        }

        Response::html($html);
    }

    /**
     * Affiche une vue d'erreur.
     *
     * @param array<string, mixed> $data
     */
    protected function renderError(
        string $file,
        int $statusCode,
        array $data = [],
    ): never {
        $viewPath = $this->errorViewPath($file);

        if (!is_file($viewPath)) {
            Response::html(
                'Vue erreur introuvable : ' . $file,
                500,
            );
        }

        $view = $data;

        $title = $this->title;

        $basePath = $this->basePath;

        $currentPath = app(
            Request::class,
        )->path();

        ob_start();

        require $viewPath;

        $content = ob_get_clean();

        if ($content === false) {
            $content = '';
        }

        $templatePath = $this->templatePath();

        if (!is_file($templatePath)) {
            Response::html(
                'Template introuvable : '
                . $this->template,
                500,
            );
        }

        ob_start();

        require $templatePath;

        $html = ob_get_clean();

        if ($html === false) {
            $html = '';
        }

        Response::html(
            $html,
            $statusCode,
        );
    }

    /**
     * Redirection simple.
     */
    protected function redirect(
        string $url,
        int $statusCode = 302,
    ): never {
        if (
            preg_match(
                '#^https?://#i',
                $url,
            ) === 1
        ) {
            Response::redirect(
                $url,
                $statusCode,
            );
        }

        Response::redirect(
            $this->basePath
            . ltrim($url, '/'),
            $statusCode,
        );
    }

    /**
     * Redirection avec erreur simple.
     */
    protected function redirectWithError(
        string $url,
        string $message,
        bool $withOld = true,
    ): never {
        if ($withOld) {
            Session::set(
                'old',
                app(Request::class)->all(),
            );
        }

        Session::set(
            'error',
            $message,
        );

        $this->redirect($url);
    }

    /**
     * @param array<string, mixed> $errors
     */
    protected function redirectWithValidationErrors(
        string $url,
        array $errors,
        string $message = 'Le formulaire contient des erreurs.',
    ): never {
        Session::set(
            'errors',
            $errors,
        );

        Session::set(
            'old',
            app(Request::class)->all(),
        );

        Session::set(
            'error',
            $message,
        );

        $this->redirect($url);
    }

    /**
     * Redirection avec succès.
     */
    protected function redirectWithSuccess(
        string $url,
        string $message,
    ): never {
        Session::set(
            'success',
            $message,
        );

        $this->redirect($url);
    }

    /**
     * Lance une 404.
     */
    public function notFound(
        string $message = 'Page introuvable',
    ): never {
        throw new NotFoundException(
            $message,
        );
    }

    /**
     * Lance une 405.
     */
    public function methodNotAllowed(
        string $message = 'Méthode non autorisée',
    ): never {
        throw new MethodNotAllowedException(
            $message,
        );
    }

    /**
     * Lance une erreur serveur.
     */
    public function serverError(
        string $message = 'Erreur interne du serveur',
    ): never {
        throw new \RuntimeException(
            $message,
        );
    }

    /**
     * Affiche réellement la page 404.
     */
    public function renderNotFoundPage(
        string $message = 'Page introuvable',
    ): never {
        $this->title =
            '404 | Page introuvable';

        $this->renderError(
            '404',
            404,
            [
                'message' => $message,
            ],
        );
    }

    /**
     * Affiche réellement la page 405.
     */
    public function renderMethodNotAllowedPage(
        string $message = 'Méthode non autorisée',
    ): never {
        $this->title =
            '405 | Méthode non autorisée';

        $this->renderError(
            '405',
            405,
            [
                'message' => $message,
            ],
        );
    }

    /**
     * Affiche réellement la page 500.
     */
    public function renderServerErrorPage(
        string $message = 'Erreur interne du serveur',
    ): never {
        $this->title =
            '500 | Erreur serveur';

        $this->renderError(
            '500',
            500,
            [
                'message' => $message,
            ],
        );
    }

    protected function isAjax(
        Request $request,
    ): bool {
        return $request->isAjax()
            || str_contains(
                $request->server(
                    'HTTP_ACCEPT',
                    '',
                ),
                'application/json',
            );
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(
        array $data,
        int $status = 200,
    ): never {
        Response::json(
            $data,
            $status,
        );
    }

    protected function ajaxOrHtml(
        Request $request,
        callable $ajax,
        callable $html,
    ): void {
        if ($this->isAjax($request)) {
            $ajax();

            return;
        }

        $html();
    }
}
