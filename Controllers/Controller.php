<?php
namespace App\Controllers;

use App\Core\Functions;

abstract class Controller
{
    protected string $template = 'base';
    protected string $title = 'LoliSSR';
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = Functions::basePath();
    }

    public function render(string $file, array $data = []): void
    {
        extract($data);

        $title = $this->title;
        $basePath = $this->basePath;

        ob_start();
        require_once(ROOT . '/Views/' . $file . '.php');

        $content = ob_get_clean();

        require_once(ROOT . '/Views/' . $this->template . '.php');
    }
}