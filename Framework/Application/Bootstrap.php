<?php
declare(strict_types=1);

namespace Framework\Application;

use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Container\AppContainer;
use Framework\Container\Container;
use Framework\Exceptions\ErrorHandler;
use Framework\Http\Request;
use Framework\Routing\Router;
use Framework\Routing\RouteCollection;

final class Bootstrap
{
    public static function run(): never
    {
        // Nettoyage
        Env::clear();
        Config::clear();
        self::loadEnvironment(base_path('.env'));
        self::configureDebug();
        ErrorHandler::register();

        // Container
        $container = new Container();
        AppContainer::set($container);
        $container->singleton(Request::class, fn(): Request => Request::capture());

        // Routes
        $collection = new RouteCollection();
        $router = new Router($collection);
        $routes = require base_path('Config/routes.php');
        if (is_callable($routes)) $routes($router);

        // Dispatch
        $router->dispatch();
        exit;
    }

    private static function loadEnvironment(string $envFile): void
    {
        if (!is_file($envFile)) return;
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $_ENV[$name] = $_SERVER[$name] = $value;
            putenv("{$name}={$value}");
        }
    }

    private static function configureDebug(): void
    {
        $debug = App::debug();
        error_reporting($debug ? E_ALL : 0);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
    }
}