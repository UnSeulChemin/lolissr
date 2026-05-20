<?php

declare(strict_types=1);

namespace App\Core\Application;

use App\Core\Config\Config;
use App\Core\Config\Env;
use App\Core\Container\AppContainer;
use App\Core\Container\Container;
use App\Core\Exceptions\ErrorHandler;
use App\Core\Http\Request;
use App\Core\Http\Router;

final class Bootstrap
{
    public static function run(): never
    {
        self::loadEnvironment(
            ROOT . '/.env',
        );

        Env::clear();
        Config::clear();

        self::configureDebug();

        ErrorHandler::register();

        $container = new Container();

        AppContainer::set(
            $container,
        );

        $container->singleton(
            Request::class,
            fn (): Request => Request::capture(),
        );

        $router = new Router();

        $routes = require app_path(
            'Config/routes.php',
        );

        if (is_callable($routes)) {
            $routes($router);
        }

        $router->dispatch();

        exit;
    }

    private static function loadEnvironment(
        string $envFile,
    ): void {
        if (!is_file($envFile)) {
            return;
        }

        $lines = file(
            $envFile,
            FILE_IGNORE_NEW_LINES
            | FILE_SKIP_EMPTY_LINES,
        );

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if (
                $line === ''
                || str_starts_with($line, '#')
                || !str_contains($line, '=')
            ) {
                continue;
            }

            [$name, $value] = explode(
                '=',
                $line,
                2,
            );

            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $value = self::normalizeEnvValue(
                trim($value),
            );

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;

            putenv(
                "{$name}={$value}",
            );
        }
    }

    private static function normalizeEnvValue(
        string $value,
    ): string {
        $length = strlen($value);

        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];

            if (
                ($first === '"' && $last === '"')
                || ($first === "'" && $last === "'")
            ) {
                return substr(
                    $value,
                    1,
                    -1,
                );
            }
        }

        return $value;
    }

    private static function configureDebug(): void
    {
        $debug = App::debug();

        error_reporting(
            $debug
                ? E_ALL
                : 0,
        );

        ini_set(
            'display_errors',
            $debug
                ? '1'
                : '0',
        );

        ini_set(
            'log_errors',
            '1',
        );
    }
}
