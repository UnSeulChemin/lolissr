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
    protected string $template = 'layouts/base';

    protected string $title;

    protected string $baseUri;

    public function __construct(
        protected Request $request,
    ) {
        $this->title = App::siteName();
        $this->baseUri = App::baseUri();
    }

    protected function viewPath(
        string $file,
    ): string {
        return view_path(
            $file . '.php',
        );
    }

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

    protected function templatePath(): string
    {
        return view_path(
            $this->template . '.php',
        );
    }

    /**
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
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sharedViewVariables(
        array $data = [],
    ): array {
        return [
            'view' => $data,
            'title' => $this->title,
            'baseUri' => $this->baseUri,
            'currentPath' => $this->request->path(),
        ];
    }

    private function ensureViewExists(
        string $path,
        string $type,
    ): void {
        if (is_file($path)) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                '%s introuvable : %s',
                $type,
                $path,
            ),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderView(
        string $viewPath,
        int $statusCode = 200,
        array $data = [],
        bool $withTemplate = true,
    ): never {
        $this->ensureViewExists(
            $viewPath,
            'Vue',
        );

        $variables = $this->sharedViewVariables(
            $data,
        );

        $content = $this->renderPhp(
            $viewPath,
            $variables,
        );

        if (!$withTemplate) {
            Response::html(
                $content,
                $statusCode,
            );
        }

        $templatePath = $this->templatePath();

        $this->ensureViewExists(
            $templatePath,
            'Template',
        );

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
                $this->baseUri,
            )
        ) {
            Response::redirect(
                $url,
                $statusCode,
            );
        }

        $baseUri = $this->baseUri === '/'
            ? ''
            : rtrim(
                $this->baseUri,
                '/',
            );

        $location = $baseUri
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
                strtolower(
                    $this->request->server(
                        'HTTP_ACCEPT',
                        '',
                    ),
                ),
                'application/json',
            );
    }

    /**
     * SUCCESS JSON ONLY
     *
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
}