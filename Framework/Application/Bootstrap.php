<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Container\AppContainer;
use Framework\Container\Container;
use Framework\Http\ErrorHandler;
use Framework\Http\Middleware\SecurityHeadersMiddleware;
use Framework\Http\Request;
use Framework\Routing\RouteCollection;
use Framework\Routing\Router;

use RuntimeException;

final class Bootstrap
{
    // =========================================
    // BOOTSTRAP
    // =========================================

    public static function loadEnvOnly(): void
    {
        Env::clear();

        self::loadEnvironment(base_path('.env'));
    }

    public static function run(): never
    {
        Env::clear();
        Config::clear();

        self::loadEnvironment(base_path('.env'));
        self::configureDebug();

        ErrorHandler::register();

    header_remove('X-Powered-By');

        $container = new Container();

        AppContainer::set($container);

        $container->singleton(Request::class, static fn (): Request => Request::capture());

        $router = new Router(new RouteCollection());

        $routes = require base_path('Config/routes.php');

        if (! is_callable($routes))
        {
            throw new RuntimeException('Config/routes.php must return a callable.');
        }

        $routes($router);

        /** @var Request $request */
        $request = $container->get(Request::class);

        /** @var SecurityHeadersMiddleware $securityHeaders */
        $securityHeaders = $container->get(SecurityHeadersMiddleware::class);

        $kernel = new AppKernel($router, $request, $securityHeaders);

        $kernel->boot();
        $kernel->handle();

        exit;
    }

    // =========================================
    // ENVIRONNEMENT
    // =========================================

    private static function loadEnvironment(string $envFile): void
    {
        if (! is_file($envFile))
        {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false)
        {
            return;
        }

        foreach ($lines as $line)
        {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '='))
            {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;

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
