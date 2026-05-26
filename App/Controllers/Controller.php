<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Common\ServiceResult;
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

        return view_path(
            'errors/'
            . ltrim($file, '/')
            . '.php',
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
    private function buildViewVariables(
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
    private function buildView(
        string $viewPath,
        array $data = [],
        bool $withTemplate = true,
    ): string {

        $this->ensureViewExists(
            $viewPath,
            'Vue',
        );

        $variables =
            $this->buildViewVariables(
                $data,
            );

        $content =
            $this->renderPhp(
                $viewPath,
                $variables,
            );

        if (! $withTemplate) {
            return $content;
        }

        $templatePath =
            $this->templatePath();

        $this->ensureViewExists(
            $templatePath,
            'Template',
        );

        return $this->renderPhp(
            $templatePath,
            [
                ...$variables,
                'content' => $content,
            ],
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

        Response::html(
            $this->buildView(
                $viewPath,
                $data,
                $withTemplate,
            ),
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
            viewPath:
                $this->viewPath($file),

            data:
                $data,
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
            viewPath:
                $this->viewPath($file),

            data:
                $data,

            withTemplate:
                false,
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
            viewPath:
                $this->errorViewPath($file),

            statusCode:
                $statusCode,

            data:
                $data,
        );
    }

    protected function redirect(
        string $url,
        int $statusCode = 302,
    ): never {

        if (
            preg_match('#^https?://#i', $url)
            || str_starts_with(
                $url,
                $this->baseUri,
            )
        ) {
            Response::redirect(
                $url,
                $statusCode,
            );
        }

        $baseUri =
            $this->baseUri === '/'
                ? ''
                : rtrim(
                    $this->baseUri,
                    '/',
                );

        Response::redirect(
            $baseUri
            . '/'
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

    protected function isAjax(): bool
    {
        return $this->request->isAjax();
    }

    protected function expectsJson(): bool
    {
        return $this->isAjax()
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
     * @param array<string, mixed> $data
     */
    protected function json(
        array $data,
        int $statusCode = 200,
    ): never {

        Response::json(
            $data,
            $statusCode,
        );
    }

    protected function jsonResult(
        ServiceResult $result,
    ): never {

        $this->json(
            $result->toArray(),
            $result->status,
        );
    }
}