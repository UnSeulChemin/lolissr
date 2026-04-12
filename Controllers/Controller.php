<?php
namespace App\Controllers;

use App\Core\Functions;

abstract class Controller
{
    /**
     * template principal utilisé pour le rendu
     */
    protected string $template = 'base';

    /**
     * titre de la page
     */
    protected string $title;

    /**
     * chemin de base du projet
     */
    protected string $basePath;

    /**
     * initialise les données communes aux controllers
     */
    public function __construct()
    {
        $this->title = Functions::siteName();
        $this->basePath = Functions::basePath();
    }

    /**
     * affiche une vue dans le template principal
     */
    public function render(string $file, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        $viewPath = ROOT . '/Views/' . $file . '.php';

        if (!file_exists($viewPath))
        {
            http_response_code(404);
            exit('Vue introuvable : ' . $file);
        }

        ob_start();
        require_once $viewPath;
        $content = ob_get_clean();

        $templatePath = ROOT . '/Views/' . $this->template . '.php';

        if (!file_exists($templatePath))
        {
            http_response_code(500);
            exit('Template introuvable : ' . $this->template);
        }

        require_once $templatePath;
    }
}