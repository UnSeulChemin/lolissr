<?php

namespace App\Core;

class Main
{
    /**
     * démarre l'application
     * gère le routing vers le bon controller
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        $basePath = Functions::basePath();
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | Redirection si slash final
        |--------------------------------------------------------------------------
        */
        if (
            $uri !== ''
            && $uri !== '/'
            && $uri !== $basePath
            && str_ends_with($uri, '/')
        )
        {
            $cleanUri = rtrim($uri, '/');

            header('Location: ' . $cleanUri, true, 301);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Récupération et nettoyage de la route
        |--------------------------------------------------------------------------
        */
        $route = $_GET['p'] ?? '';
        $route = trim($route);
        $route = trim($route, '/');
        $route = filter_var($route, FILTER_SANITIZE_URL);

        $params = ($route === '')
            ? ['']
            : explode('/', $route);

        /*
        |--------------------------------------------------------------------------
        | Page d'accueil
        |--------------------------------------------------------------------------
        */
        if ($params[0] === '')
        {
            $controller = new \App\Controllers\MainController();
            $controller->index();
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Construction du controller
        |--------------------------------------------------------------------------
        */
        $controllerName = ucfirst(array_shift($params)) . 'Controller';
        $controller = '\\App\\Controllers\\' . $controllerName;

        /*
        |--------------------------------------------------------------------------
        | Action à appeler
        |--------------------------------------------------------------------------
        */
        $action = (isset($params[0]) && $params[0] !== '')
            ? array_shift($params)
            : 'index';

        /*
        |--------------------------------------------------------------------------
        | Vérifie controller + méthode
        |--------------------------------------------------------------------------
        */
        if (class_exists($controller) && method_exists($controller, $action))
        {
            $controllerInstance = new $controller();

            if (!empty($params))
            {
                call_user_func_array([$controllerInstance, $action], $params);
            }
            else
            {
                $controllerInstance->$action();
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Page introuvable
        |--------------------------------------------------------------------------
        */
        $controller = new \App\Controllers\MainController();
        $controller->renderError('404', 404);
    }
}