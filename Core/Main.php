<?php
namespace App\Core;

class Main
{
    public function start(): void
    {
        session_start();

        $config = require __DIR__ . '/../Config/config.php';
        $basename = $config['basename'];

        $uri = $_SERVER['REQUEST_URI'];

        if (!empty($uri) && $uri !== '/' && substr($uri, -1) === '/' && $uri !== $basename)
        {
            $uri = substr($uri, 0, -1);

            http_response_code(301);
            header('Location: ' . $uri);
            exit;
        }

        $params = explode('/', $_GET['p'] ?? '');
        if ($params[0] === '')
        {
            $controller = new \App\Controllers\MainController();
            $controller->index();
            return;
        }

        // page d'accueil
        if ($params[0] === '')
        {
            $controller = new \App\Controllers\MainController();
            $controller->index();
            return;
        }

        $controller = '\\App\\Controllers\\' . ucfirst(array_shift($params)) . 'Controller';
        $action = isset($params[0]) && $params[0] !== '' ? array_shift($params) : 'index';

        if (class_exists($controller) && method_exists($controller, $action))
        {
            $controller = new $controller();

            !empty($params)
                ? call_user_func_array([$controller, $action], $params)
                : $controller->$action();

            return;
        }

        http_response_code(404);
        echo 'Page introuvable';
    }
}