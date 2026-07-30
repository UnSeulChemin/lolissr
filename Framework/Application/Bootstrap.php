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
    private function __construct()
    {
    }

    // =========================================
    // BOOTSTRAP
    // =========================================

    public static function loadEnvOnly(): void
    {
        Env::load(base_path('.env'));
        Config::clear();

        EnvironmentValidator::validate();
    }

    /**
     * @param (callable(int, string, Request): never)|null $errorRenderer
     */
    public static function run(?callable $errorRenderer = null): never
    {
        self::loadEnvOnly();

        RequestContext::start();

        self::configureTimezone();
        self::configureDebug();
        self::configureErrorHandler($errorRenderer);

        header_remove('X-Powered-By');

        self::startProfiler();

        $container = self::createContainer();
        $router = self::createRouter($container);

        self::registerRoutes($router);

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
    // CONTAINER
    // =========================================

    private static function createContainer(): Container
    {
        $container = new Container();

        AppContainer::set($container);

        $container->singleton(
            Request::class,
            static fn (): Request => Request::capture()
        );

        $container->singleton(Database::class);

        return $container;
    }

    // =========================================
    // ROUTER
    // =========================================

    private static function createRouter(Container $container): Router
    {
        return new Router(
            new RouteCollection(),
            $container
        );
    }

    private static function registerRoutes(Router $router): void
    {
        $routes = require base_path('Config/routes.php');

        if (! is_callable($routes))
        {
            throw new RuntimeException('Config/routes.php must return a callable.');
        }

        $routes($router);
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

    /**
     * @param (callable(int, string, Request): never)|null $renderer
     */
    private static function configureErrorHandler(?callable $renderer): void
    {
        if ($renderer !== null)
        {
            ErrorHandler::setRenderer($renderer);
        }

        ErrorHandler::register();
    }

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
            throw new RuntimeException('Invalid application timezone: ' . $timezone);
        }
    }
}