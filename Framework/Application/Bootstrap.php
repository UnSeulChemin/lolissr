<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Config\EnvironmentValidator;
use Framework\Container\AppContainer;
use Framework\Container\Container;
use Framework\Database\Database;
use Framework\Debug\Profiler;
use Framework\Http\ErrorHandler;
use Framework\Http\Middleware\SecurityHeadersMiddleware;
use Framework\Http\Request;
use Framework\Http\RequestContext;
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
        Env::load(base_path('.env'));
        Config::clear();

        EnvironmentValidator::validate();
    }

    public static function run(): never
    {
        Env::load(base_path('.env'));
        Config::clear();

        EnvironmentValidator::validate();

        RequestContext::start();

        self::configureTimezone();
        self::configureDebug();

        ErrorHandler::register();

        header_remove('X-Powered-By');

        self::startProfiler();

        $container = new Container();

        AppContainer::set($container);

        $container->singleton(
            Request::class,
            static fn (): Request => Request::capture()
        );

        $container->singleton(Database::class);

        $router = new Router(
            new RouteCollection(),
            $container
        );

        $routes = require base_path('Config/routes.php');

        if (! is_callable($routes))
        {
            throw new RuntimeException(
                'Config/routes.php must return a callable.'
            );
        }

        $routes($router);

        /** @var Request $request */
        $request = $container->get(Request::class);

        /** @var SecurityHeadersMiddleware $securityHeaders */
        $securityHeaders = $container->get(
            SecurityHeadersMiddleware::class
        );

        $kernel = new AppKernel(
            $router,
            $request,
            $securityHeaders
        );

        $kernel->boot();
        $kernel->handle();

        exit;
    }

    // =========================================
    // PROFILER
    // =========================================

    private static function startProfiler(): void
    {
        if (! App::debug() || ! env_bool('PROFILER_ENABLED', false))
        {
            return;
        }

        Profiler::startRequest();

        register_shutdown_function(
            static function (): void
            {
                $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
                $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
                $status = http_response_code();

                Profiler::finishRequest(
                    method: $method,
                    uri: $uri,
                    status: is_int($status) ? $status : 200
                );
            }
        );
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    private static function configureDebug(): void
    {
        $debug = App::debug();

        error_reporting($debug ? E_ALL : 0);

        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
    }

    private static function configureTimezone(): void
    {
        $timezone = App::timezone();

        if (! date_default_timezone_set($timezone))
        {
            throw new RuntimeException(
                'Invalid application timezone: ' . $timezone
            );
        }
    }
}