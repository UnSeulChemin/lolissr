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
use Throwable;

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

        return view_path(
            'errors/' . ($file ?? '') . '.php',
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

        try {
            require $path;

            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
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
     * Pipeline central de rendu HTML.
     *
     * @param array<string, mixed> $data
     */
    private function renderView(
        string $viewPath,
        int $statusCode = 200,
        array $data = [],
        bool $withTemplate = true,
    ): never {
        // Correction importante :
        // une vue manquante = erreur serveur,
        // PAS une 404 HTTP.
        if (!is_file($viewPath)) {
            throw new RuntimeException(
                'Vue introuvable : ' . $viewPath,
            );
        }

        $variables = $this->sharedViewVariables(
            $data,
        );

        $content = $this->renderPhp(
            $viewPath,
            $variables,
        );

        // Correction importante :
        // Response::html() termine probablement le script.
        // Donc il faut return explicitement.
        if (!$withTemplate) {
            Response::html(
                $content,
                $statusCode,
            );
        }

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

    /**
     * @param array<string, mixed> $data
     */
    protected function render(
        string $file,
        array $data = [],
    ): never {
        $this->renderView(
            viewPath: $this->viewPath($file),
            data: $data,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderPartial(
        string $file,
        array $data = [],
    ): never {
        $this->renderView(
            viewPath: $this->viewPath($file),
            data: $data,
            withTemplate: false,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderError(
        string $file,
        int $statusCode,
        array $data = [],
    ): never {
        $this->renderView(
            viewPath: $this->errorViewPath($file),
            statusCode: $statusCode,
            data: $data,
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

        if (
            str_starts_with(
                $url,
                $this->basePath,
            )
        ) {
            Response::redirect(
                $url,
                $statusCode,
            );
        }

        $location = rtrim(
            $this->basePath,
            '/',
        )
        . '/'
        . ltrim(
            $url,
            '/',
        );

        Response::redirect(
            $location,
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