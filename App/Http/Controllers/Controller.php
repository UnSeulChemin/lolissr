<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Common\Responses\FlashToastData;
use App\DTO\Common\Responses\FormViewData;
use App\DTO\Common\Responses\ViewData;
use App\DTO\Common\ServiceResult;
use App\Support\Assets\PageStyles;

use Framework\Config\ApplicationConfig;
use Framework\Debug\Profiler;
use Framework\Http\Exceptions\ValidationException;
use Framework\Http\Requests\FormRequest;
use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Session;

use RuntimeException;
use Throwable;

abstract class Controller
{
    protected string $template = 'layouts/base';

    protected string $title;

    protected string $baseUri;

    private ?FlashToastData $flashToast = null;

    protected function stringInput(string $key, string $default = ''): string
    {
        $value = $this->request->input($key, $default);
        if (! is_string($value))
        {
            throw new ValidationException([$key => 'Ce champ doit être une chaîne de caractères.']);
        }

        return $value;
    }

    // Accepter uniquement les chemins internes relatifs à l’application ; utiliser le repli de l’appelant sinon.
    protected function returnPathInput(): string
    {
        $value = $this->stringInput('return_to', '');
        if (str_contains($value, '\\') || preg_match('/[\x00-\x20\x7f]/', $value) === 1) return '';
        $path = explode('?', explode('#', $value, 2)[0], 2)[0];
        return preg_match('#^[a-zA-Z0-9][a-zA-Z0-9/_-]*$#D', $path) === 1 ? $value : '';
    }

    public function __construct(protected Request $request)
    {
        $this->title = ApplicationConfig::siteName();
        $this->baseUri = rtrim(base_uri(), '/');
    }

    // --------------------------------------------------------------------------
    // REQUÊTE
    // --------------------------------------------------------------------------

    protected function isAjax(): bool
    {
        return $this->request->isAjax();
    }

    protected function expectsJson(): bool
    {
        return $this->request->expectsJson();
    }

    // Les valeurs invalides ou absentes restent invalides pour la validation 0/1 des services.
    protected function binaryStatusInput(string $key): int
    {
        $value = $this->request->input($key);

        return in_array($value, [0, 1, '0', '1'], true) ? (int) $value : -1;
    }

    // --------------------------------------------------------------------------
    // VUES
    // --------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $file, array $data = []): never
    {
        $this->respondView($this->viewPath($file), data: $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderFragment(string $file, array $data = []): never
    {
        Response::html($this->renderContent($this->viewPath($file), $data, false));
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderError(string $file, int $statusCode, array $data = []): never
    {
        $this->respondView($this->errorViewPath($file), $statusCode, $data);
    }

    // --------------------------------------------------------------------------
    // JSON
    // --------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $statusCode = 200): never
    {
        Response::json($data, $statusCode);
    }

    protected function jsonResult(ServiceResult $result): never
    {
        $this->json($result->toArray(), $result->status);
    }

    // --------------------------------------------------------------------------
    // DONNÉES DE VUE
    // --------------------------------------------------------------------------

    protected function viewData(): ViewData
    {
        return new ViewData(baseUri: view_base_uri(), toast: $this->flashToastData());
    }

    protected function formViewData(string $formAction, string $cancelUrl): FormViewData
    {
        $build = fn (): FormViewData => new FormViewData(
            baseUri: view_base_uri(),
            toast: $this->flashToastData(),
            errors: $this->request->header('X-Prefetch') === 'true' ? [] : Session::pull('errors', []),
            old: $this->request->header('X-Prefetch') === 'true' ? [] : Session::pull('old', []),
            formAction: $this->url($formAction),
            cancelUrl: $this->url($cancelUrl)
        );

        return $this->request->header('X-Prefetch') === 'true'
            ? $build()
            : Session::withLock($build);
    }

    protected function flashToastData(): FlashToastData
    {
        if ($this->request->header('X-Prefetch') === 'true')
        {
            return new FlashToastData(null, null);
        }
        if ($this->flashToast !== null)
        {
            return $this->flashToast;
        }

        [$success, $error] = Session::withLock(static fn (): array => [Session::pull('success'), Session::pull('error')]);

        if (is_string($success))
        {
            return $this->flashToast =
                new FlashToastData(message: $success, type: 'success');
        }

        if (is_string($error))
        {
            return $this->flashToast =
                new FlashToastData(message: $error, type: 'error');
        }

        return $this->flashToast =
            new FlashToastData(message: null, type: null);
    }

    // --------------------------------------------------------------------------
    // VALIDATION
    // --------------------------------------------------------------------------

    protected function validateRequest(FormRequest $request): void
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }
    }

    // --------------------------------------------------------------------------
    // REDIRECTIONS
    // --------------------------------------------------------------------------

    protected function redirect(string $url, int $statusCode = 302): never
    {
        $redirectUrl = $this->isAbsoluteUrl($url)
            ? $url
            : $this->url($url);

        if ($this->expectsJson())
        {
            Response::json(['success' => true, 'type' => 'redirect', 'redirect' => $redirectUrl]);
        }

        Response::redirect($redirectUrl, $statusCode);
    }

    /**
     * @param array<string, mixed> $session
     */
    protected function redirectWith(string $url, array $session): never
    {
        foreach ($session as $key => $value)
        {
            Session::set($key, $value);
        }

        $this->redirect($url);
    }

    protected function redirectWithError(string $url, string $message, bool $withOld = true): never
    {
        $session = ['error' => $message];

        if ($withOld)
        {
            $session['old'] = $this->oldInput();
        }

        $this->redirectWith($url, $session);
    }

    protected function redirectWithSuccess(string $url, string $message): never
    {
        $this->redirectWith($url, ['success' => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    private function oldInput(): array
    {
        $input = $this->request->all();

        unset($input['password'], $input['password_confirmation'], $input['current_password']);

        return $input;
    }

    // --------------------------------------------------------------------------
    // CHEMINS DES VUES
    // --------------------------------------------------------------------------

    private function viewPath(string $file): string
    {
        return view_path(ltrim($file, '/') . '.php');
    }

    private function errorViewPath(string $file): string
    {
        return $this->viewPath('errors/' . ltrim($file, '/'));
    }

    private function templatePath(): string
    {
        return $this->viewPath($this->template);
    }

    // --------------------------------------------------------------------------
    // RENDU DES VUES
    // --------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     */
    private function respondView(string $viewPath, int $statusCode = 200, array $data = [], bool $withTemplate = true): never
    {
        $fragment = $this->expectsJson() && $this->request->header('X-Page-Format') === 'fragment';
        $html = $this->renderContent($viewPath, $data, $withTemplate && ! $fragment);

        if ($this->expectsJson())
        {
            Response::json(
                [
                    'success' => true,
                    'type' => 'page',
                    'page' => [
                        'html' => $html,
                        'format' => $fragment ? 'fragment' : 'document',
                        'lang' => 'fr',
                        'bodyData' => (object) [],
                        'title' => $this->title,
                        'url' => $this->request->uri(),
                        'stylesheets' => PageStyles::forView($viewPath),
                        'flashToast' => $this->flashToastData(),
                        'requiresFreshNavigation' => $this->request->header('X-Prefetch') === 'true'
                            && Session::withLock(static fn (): bool =>
                                Session::has('success') || Session::has('error')
                                || Session::has('errors') || Session::has('old'))
                    ]
                ],
                $statusCode
            );
        }

        Response::html($html, $statusCode);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderContent(string $viewPath, array $data = [], bool $withTemplate = true): string
    {
        return \App\Support\Media\ImageAssets::withFingerprints(fn (): string => Profiler::measure(
            'view.render',
            function () use ($viewPath, $data, $withTemplate): string
            {
                $this->ensureViewExists($viewPath);

                $variables = $this->baseViewData($data);

                $content = Profiler::measure('view.content', fn (): string => $this->renderPhp($viewPath, $variables));

                if (! $withTemplate)
                {
                    return $content;
                }

                $templatePath = $this->templatePath();

                $this->ensureViewExists($templatePath);

                return Profiler::measure(
                    'view.template',
                    fn (): string => $this->renderPhp(
                        $templatePath,
                        [...$variables, 'content' => $content, 'pageStylesheets' => PageStyles::forView($viewPath)]
                    )
                );
            }
        ));
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function renderPhp(string $path, array $variables = []): string
    {
        extract($variables, EXTR_SKIP);

        ob_start();

        try
        {
            require $path;

            $content = ob_get_clean();

            return is_string($content)
                ? $content
                : '';
        }
        catch (Throwable $exception)
        {
            if (ob_get_level() > 0)
            {
                ob_end_clean();
            }

            throw $exception;
        }
    }

    private function ensureViewExists(string $path): void
    {
        if (! is_file($path))
        {
            throw new RuntimeException(
                "Vue introuvable : {$path}"
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function baseViewData(array $data = []): array
    {
        return ['view' => $this->viewData(), 'title' => $this->title, 'currentPath' => $this->request->path(), ...$data];
    }

    // --------------------------------------------------------------------------
    // URLS
    // --------------------------------------------------------------------------

    private function url(string $path): string
    {
        return $this->baseUri
            . '/'
            . ltrim($path, '/');
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return preg_match('#^https?://#i', $url) === 1;
    }
}
