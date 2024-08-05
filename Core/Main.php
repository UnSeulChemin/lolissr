<?php
namespace App\Core;

use App\Controllers\MainController;

class Main
{
    /**
     * view router
     * @return void
     */
    public function start(): void
    {
        session_start();

        $uri = $_SERVER['REQUEST_URI'];
		$basename = '/lolissr/';

        if (!empty($uri) && $uri != '/' && $uri[-1] === '/' && $uri != $basename)
        {
            $uri = substr($uri, 0, -1);

            http_response_code(301);
            header('Location: '.$uri);
            exit;
        }

        $params = explode('/', $_GET['p']);

        if ($params[0] != '')
        {
            $controller = '\\App\\Controllers\\'.ucfirst(array_shift($params)).'Controller';
            $action = isset($params[0]) ? array_shift($params) : 'index';

            if (method_exists($controller, $action))
            {
                $controller = new $controller();
                (isset($params[0])) ? call_user_func_array([$controller,$action], $params) : $controller->$action();
            }
            
            else
            {
                http_response_code(404);
                header('Location: ./');
                exit;
            }
        }
        
        else
        {
            $controller = new MainController();
            $controller->index();
        }
    }
}