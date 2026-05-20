<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Application\App;
use Framework\Exceptions\MethodNotAllowedException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Support\Session;
use RuntimeException;

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

    public function __construct(
        protected Request $request,
    ) {
        $this->title = App::siteName();
        $this->basePath = App::basePath();
    }

    /**
     * Retourne le chemin complet d'une vue standard.
     */
    protected function viewPath(
        string $file,
    ): string {
        return view_path(
            $file . '.php',
        );
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
     * Capture le rendu d'un fichier PHP.
     *
     * @param array<string, mixed> $variables
     */
    private function renderPhp(
        string $path,
        array $variables = [],
    ): string {
        extract(
            $variables,
            EXTR_SKIP,
        );

        ob_start();

        require $path;

        $content = ob_get_clean();

        return is_string($content)
            ? $content
            : '';
    }

    /**
     * Variables communes aux vues.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sharedViewVariables(
        array $data = [],
    ): array {
        return [
            'view' => $data,
            'title' => $this->title,
            'basePath' => $this->basePath,
            'currentPath' => $this->request->path(),
        ];
    }

    /**
     * @param array<string, mixed>|object $data
     */
    protected function render(
        string $file,
        array|object $data = [],
    ): never {
        $viewPath = $this->viewPath(
            $file,
        );

        if (!is_file($viewPath)) {
            throw new NotFoundException(
                'Vue introuvable : ' . $file,
            );
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        $variables = $this->sharedViewVariables(
            $data,
        );

        $content = $this->renderPhp(
            $viewPath,
            $variables,
        );

        $templatePath = $this->templatePath();

        if (!is_file($templatePath)) {
            throw new RuntimeException(
                'Template introuvable : '
                . $this->template,
            );
        }

        $html = $this->renderPhp(
            $templatePath,
            array_merge(
                $variables,
                [
                    'content' => $content,
                ],
            ),
        );

        Response::html($html);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderPartial(
        string $file,
        array $data = [],
    ): never {
        $viewPath = $this->viewPath(
            $file,
        );

        if (!is_file($viewPath)) {
            throw new NotFoundException(
                'Vue partielle introuvable : '
                . $file,
            );
        }

        $html = $this->renderPhp(
            $viewPath,
            $this->sharedViewVariables(
                $data,
            ),
        );

        Response::html($html);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderError(
        string $file,
        int $statusCode,
        array $data = [],
    ): never {
        $viewPath = $this->errorViewPath(
            $file,
        );

        if (!is_file($viewPath)) {
            throw new RuntimeException(
                'Vue erreur introuvable : '
                . $file,
            );
        }

        $variables = $this->sharedViewVariables(
            $data,
        );

        $content = $this->renderPhp(
            $viewPath,
            $variables,
        );

        $templatePath = $this->templatePath();

        if (!is_file($templatePath)) {
            throw new RuntimeException(
                'Template introuvable : '
                . $this->template,
            );
        }

        $html = $this->renderPhp(
            $templatePath,
            array_merge(
                $variables,
                [
                    'content' => $content,
                ],
            ),
        );

        Response::html(
            $html,
            $statusCode,
        );
    }

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

    protected function redirectWithError(
        string $url,
        string $message,
        bool $withOld = true,
    ): never {
        if ($withOld) {
            Session::set(
                'old',
                $this->request->all(),
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
            $this->request->all(),
        );

        Session::set(
            'error',
            $message,
        );

        $this->redirect($url);
    }

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

    public function notFound(
        string $message = 'Page introuvable',
    ): never {
        throw new NotFoundException(
            $message,
        );
    }

    public function methodNotAllowed(
        string $message = 'Méthode non autorisée',
    ): never {
        throw new MethodNotAllowedException(
            $message,
        );
    }

    public function serverError(
        string $message = 'Erreur interne du serveur',
    ): never {
        throw new RuntimeException(
            $message,
        );
    }

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

    protected function isAjax(): bool
    {
        return $this->request->isAjax()
            || str_contains(
                $this->request->server(
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
        callable $ajax,
        callable $html,
    ): void {
        if ($this->isAjax()) {
            $ajax();

            return;
        }

        $html();
    }
}