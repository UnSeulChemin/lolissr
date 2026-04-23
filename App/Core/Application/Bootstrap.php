<?php

declare(strict_types=1);

namespace App\Core\Application;

use App\Core\Config\Config;
use App\Core\Config\Env;
use App\Core\Exceptions\ErrorHandler;
use App\Core\Http\Router;

final class Bootstrap
{
    /**
     * Lance l'application.
     */
    public static function run(): void
    {
        self::loadEnvironment(ROOT . '/.env');
        require_once ROOT . '/App/Core/Support/helpers.php';

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

        $router->dispatch();
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

            $value = self::normalizeEnvValue($value);

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv($name . '=' . $value);
        }
    }

    /**
     * Normalise une valeur de variable d'environnement.
     */
    private static function normalizeEnvValue(string $value): string
    {
        $value = trim($value);

        $length = strlen($value);

        if ($length >= 2)
        {
            $firstChar = $value[0];
            $lastChar = $value[$length - 1];

            if (
                ($firstChar === '"' && $lastChar === '"')
                || ($firstChar === "'" && $lastChar === "'")
            )
            {
                return substr($value, 1, -1);
            }
        }

        return $value;
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