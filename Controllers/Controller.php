<?php

namespace App\Controllers;

use App\Core\Functions;

abstract class Controller
{
    protected string $template = 'layouts/base';
    protected string $title;
    protected string $basePath;

    public function __construct()
    {
        $this->title = Functions::siteName();
        $this->basePath = Functions::basePath();
    }

    public function render(string $file, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        $viewPath = ROOT . '/Views/' . $file . '.php';

        if (!is_file($viewPath))
        {
            http_response_code(404);
            exit('Vue introuvable : ' . $file);
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $templatePath = ROOT . '/Views/' . $this->template . '.php';

        if (!is_file($templatePath))
        {
            http_response_code(500);
            exit('Template introuvable : ' . $this->template);
        }

        require $templatePath;
    }

    public function renderError(string $file, int $statusCode, array $data = []): void
    {
        http_response_code($statusCode);

        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        $viewPath = ROOT . '/Views/errors/' . $file . '.php';

        if (!is_file($viewPath))
        {
            exit('Vue erreur introuvable : ' . $file);
        }

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
}