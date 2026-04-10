<?php
namespace App\Controllers;

use App\Core\Functions;

abstract class Controller
{
    protected $template = 'base';
    protected $title = 'LoliSSR';

    public function render(string $file, array $data = []): void
    {
        extract($data);

        $title = $this->title;

        // base path global
        $basePath = Functions::basePath();

        ob_start();
        require_once(ROOT.'/Views/'.$file.'.php');

        $content = ob_get_clean();

        require_once(ROOT.'/Views/'.$this->template.'.php');
    }
}