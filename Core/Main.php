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
        /* démarre la session */
        session_start();

        /* récup chemin base depuis config */
        $basePath = Functions::basePath();

        /* récup URL actuelle */
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        /**
         * supprime le slash final
         * ex: /manga/ → /manga
         */
        if ($uri !== '' && $uri !== '/' && $uri !== $basePath && str_ends_with($uri, '/'))
        {
            $uri = rtrim($uri, '/');

            http_response_code(301);
            header('Location: ' . $uri);
            exit;
        }

        /**
         * récup paramètres de l'URL
         * ex: manga/collection/1
         */
        $params = explode('/', $_GET['p'] ?? '');

        /**
         * si aucune page demandée
         * → accueil
         */
        if ($params[0] === '')
        {
            $controller = new \App\Controllers\MainController();
            $controller->index();
            return;
        }

        /**
         * construit le nom du controller
         * ex: manga → MangaController
         */
        $controller = '\\App\\Controllers\\' . ucfirst(array_shift($params)) . 'Controller';

        /**
         * récup méthode à appeler
         * sinon index par défaut
         */
        $action = isset($params[0]) && $params[0] !== '' ? array_shift($params) : 'index';

        /**
         * vérifie que controller + méthode existent
         */
        if (class_exists($controller) && method_exists($controller, $action))
        {
            $controller = new $controller();

            /**
             * appelle méthode avec paramètres
             * ex: manga/edit/rave/01
             */
            if (!empty($params))
            {
                call_user_func_array([$controller, $action], $params);
            }
            else
            {
                $controller->$action();
            }

            return;
        }

        /**
         * page introuvable
         */
        http_response_code(404);
        exit('Page introuvable');
    }
}