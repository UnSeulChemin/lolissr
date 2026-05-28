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
    protected string $template =
        'layouts/base';

    protected string $title;

    protected string $baseUri;

    public function __construct(
        protected Request $request,
    ) {
        $this->title =
            App::siteName();

        $this->baseUri =
            base_uri();
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW PATH
    |--------------------------------------------------------------------------
    */

    protected function viewPath(
        string $file,
    ): string {

        return view_path(
            ltrim(
                $file,
                '/',
            ) . '.php',
        );
    }

    protected function errorViewPath(
        string $file,
    ): string {

        return $this->viewPath(
            'errors/' . $file,
        );
    }

    protected function templatePath(): string
    {
        return $this->viewPath(
            $this->template,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PHP RENDER
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    private function ensureViewExists(
        string $path,
    ): void {

        if (is_file($path)) {
            return;
        }

        throw new RuntimeException(
            "Vue introuvable : {$path}",
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BASE DATA
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function baseViewData(
        array $data = [],
    ): array {

        return [
            'view' =>
                $data,

            'title' =>
                $this->title,

            'baseUri' =>
                $this->baseUri,

            'currentPath' =>
                $this->request->path(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER CONTENT
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $data
     */
    private function renderContent(
        string $viewPath,
        array $data = [],
        bool $withTemplate = true,
    ): string {

        $this->ensureViewExists(
            $viewPath,
        );

        $variables =
            $this->baseViewData(
                $data,
            );

        $content =
            $this->renderPhp(
                $viewPath,
                $variables,
            );

        /*
        |--------------------------------------------------------------------------
        | PARTIAL
        |--------------------------------------------------------------------------
        */

        if (! $withTemplate) {
            return $content;
        }

        $templatePath =
            $this->templatePath();

        $this->ensureViewExists(
            $templatePath,
        );

        return $this->renderPhp(
            $templatePath,
            [
                ...$variables,

                'content' =>
                    $content,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PAGE RESPONSE
    |--------------------------------------------------------------------------
    */

    private function respondView(
        string $viewPath,
        int $statusCode = 200,
        array $data = [],
        bool $withTemplate = true,
    ): never {

        $html =
            $this->renderContent(
                $viewPath,
                $data,
                $withTemplate,
            );

        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        if (
            $this->request->isAjax()
        ) {

            Response::json(
                [
                    'success' => true,

                    'type' => 'page',

                    'page' => [
                        'html' =>
                            $html,

                        'title' =>
                            $this->title,

                        'url' =>
                            $this->request->uri(),
                    ],
                ],
                $statusCode,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SSR
        |--------------------------------------------------------------------------
        */

        Response::html(
            $html,
            $statusCode,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $data
     */
    protected function render(
        string $file,
        array $data = [],
    ): never {

        $this->respondView(
            viewPath:
                $this->viewPath(
                    $file,
                ),

            data:
                $data,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PARTIAL
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $data
     */
    protected function renderFragment(
        string $file,
        array $data = [],
    ): never {

        Response::html(
            $this->renderContent(
                $this->viewPath(
                    $file,
                ),
                $data,
                false,
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ERROR
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $data
     */
    protected function renderError(
        string $file,
        int $statusCode,
        array $data = [],
    ): never {

        $this->respondView(
            viewPath:
                $this->errorViewPath(
                    $file,
                ),

            statusCode:
                $statusCode,

            data:
                $data,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    protected function redirect(
        string $url,
        int $statusCode = 302,
    ): never {

        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        if (
            $this->request->isAjax()
        ) {

            Response::json(
                [
                    'success' =>
                        true,

                    'type' =>
                        'redirect',

                    'redirect' =>
                        preg_match(
                            '#^https?://#i',
                            $url,
                        )
                            ? $url
                            : $this->baseUri
                                . '/'
                                . ltrim(
                                    $url,
                                    '/',
                                ),
                ],
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SSR
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '#^https?://#i',
                $url,
            )
        ) {

            Response::redirect(
                $url,
                $statusCode,
            );
        }

        Response::redirect(
            $this->baseUri
            . '/'
            . ltrim(
                $url,
                '/',
            ),
            $statusCode,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION REDIRECT
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $session
     */
    protected function redirectWith(
        string $url,
        array $session,
    ): never {

        foreach (
            $session as $key => $value
        ) {

            Session::set(
                $key,
                $value,
            );
        }

        $this->redirect(
            $url,
        );
    }

    protected function redirectWithError(
        string $url,
        string $message,
        bool $withOld = true,
    ): never {

        $session = [
            'error' =>
                $message,
        ];

        if ($withOld) {

            $session['old'] =
                $this->request->all();
        }

        $this->redirectWith(
            $url,
            $session,
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    protected function redirectWithValidationErrors(
        string $url,
        array $errors,
        string $message =
            'Le formulaire contient des erreurs.',
    ): never {

        $this->redirectWith(
            $url,
            [
                'errors' =>
                    $errors,

                'old' =>
                    $this->request->all(),

                'error' =>
                    $message,
            ],
        );
    }

    protected function redirectWithSuccess(
        string $url,
        string $message,
    ): never {

        $this->redirectWith(
            $url,
            [
                'success' =>
                    $message,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXCEPTIONS
    |--------------------------------------------------------------------------
    */

    public function notFound(
        string $message =
            'Page introuvable',
    ): never {

        throw new NotFoundException(
            $message,
        );
    }

    public function methodNotAllowed(
        string $message =
            'Méthode non autorisée',
    ): never {

        throw new MethodNotAllowedException(
            $message,
        );
    }

    public function serverError(
        string $message =
            'Erreur interne du serveur',
    ): never {

        throw new RuntimeException(
            $message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    protected function isAjax(): bool
    {
        return $this->request->isAjax();
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