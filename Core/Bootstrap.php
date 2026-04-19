<?php

declare(strict_types=1);

namespace App\Core;

final class Bootstrap
{
    /**
     * Lance l'application.
     */
    public static function run(): void
    {
        self::loadEnvironment(ROOT . '/.env');

        Env::clear();
        Config::clear();

        self::configureDebug();
        ErrorHandler::register();

        $router = new Router();

        $routes = require ROOT . '/Config/routes.php';

        if (is_callable($routes))
        {
            $routes($router);
        }

        $router->dispatch(
            $_SERVER['REQUEST_URI'] ?? '/',
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
    }

    /**
     * Charge les variables d'environnement depuis le fichier .env.
     */
    private static function loadEnvironment(string $envFile): void
    {
        if (!is_file($envFile))
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

            if (
                $line === ''
                || str_starts_with($line, '#')
                || !str_contains($line, '=')
            )
            {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            if ($name === '')
            {
                continue;
            }

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv($name . '=' . $value);
        }
    }

    /**
     * Configure le mode debug PHP.
     */
    private static function configureDebug(): void
    {
        $debug = App::debug();

        error_reporting($debug ? E_ALL : 0);
        ini_set('display_errors', $debug ? '1' : '0');
    }
}