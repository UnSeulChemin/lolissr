<?php
namespace App\Controllers;

use App\Core\Functions;

abstract class Controller
{
    /**
     * template principal utilisé (base.php)
     */
    protected string $template = 'base';

    /**
     * titre par défaut
     */
    protected string $title = 'LoliSSR';

    /**
     * chemin de base du site (/lolissr/)
     */
    protected string $basePath;

    /**
     * constructeur
     * initialise le basePath depuis la config
     */
    public function __construct()
    {
        $this->basePath = Functions::basePath();
    }

    public function render(string $file, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $title = $this->title;
        $basePath = $this->basePath;

        /* chemin vue */
        $viewPath = ROOT . '/Views/' . $file . '.php';

        if (!file_exists($viewPath))
        {
            http_response_code(404);
            exit('Vue introuvable : ' . $file);
        }

        ob_start();

        require_once $viewPath;

        $content = ob_get_clean();

        /* chemin template */
        $templatePath = ROOT . '/Views/' . $this->template . '.php';

        if (!file_exists($templatePath))
        {
            http_response_code(500);
            exit('Template introuvable : ' . $this->template);
        }

        require_once $templatePath;
    }
}