<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Config\ApplicationConfig;
use Framework\Config\Config;
use Framework\Config\Environment;
use Framework\Config\EnvironmentValidator;
use Framework\Container\Container;
use Framework\Container\ContainerRegistry;
use Framework\Database\Database;
use Framework\Debug\Profiler;
use Framework\Http\Errors\HttpErrorHandler;
use Framework\Http\Middleware\SecurityHeadersMiddleware;
use Framework\Http\Requests\Request;
use Framework\Http\Requests\RequestContext;
use Framework\Routing\RouteCollection;
use Framework\Routing\Router;

use RuntimeException;
use Throwable;

final class Bootstrap
{
    private function __construct()
    {
    }

    // =================================================
    // AMORÇAGE
    // =================================================

    public static function loadEnvOnly(): void
    {
        Environment::load(base_path('.env'));
        Config::clear();

        EnvironmentValidator::validate();
    }

    /**
     * @param (callable(int, string, Request): never)|null $errorRenderer
     * @param (callable(Container): void)|null $serviceProvider
     */
    public static function run(?callable $errorRenderer = null, ?callable $serviceProvider = null): never
    {
        $startedAt = hrtime(true);
        // Les erreurs de configuration ne peuvent pas dépendre du conteneur, du journal ou du moteur de rendu.
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);
        header_remove('X-Powered-By');
        try
        {
            RequestContext::start();
            SecurityHeadersMiddleware::applyBaseline();
            Environment::load(base_path('.env'));
            Config::clear();
            $compiled = BootstrapCache::load(BootstrapCache::path());
            if ($compiled !== null)
            {
                Config::prime($compiled['config']);
            }
            else
            {
                EnvironmentValidator::validate();
            }
            self::configureTimezone();
            (new SecurityHeadersMiddleware())->handle(Request::capture());
        }
        catch (Throwable $exception)
        {
            error_log('Application bootstrap failed: ' . $exception);
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8', true);
            echo 'Une erreur interne est survenue.';
            exit;
        }
        self::configureErrorHandler($errorRenderer);
        self::configureDebug();
        self::startProfiler($startedAt);

        $container = self::createContainer();

        self::registerServices($container, $serviceProvider);

        $router = new Router($compiled['routes'] ?? new RouteCollection(), $container);

        if ($compiled === null) self::registerRoutes($router);

        /** @var Request $request */
        $request = $container->get(Request::class);

        // Validate JSON before routing, authentication and CSRF checks.
        $request->postAll();

        /** @var SecurityHeadersMiddleware $securityHeaders */
        $securityHeaders = $container->get(SecurityHeadersMiddleware::class);

        $kernel = new HttpKernel($router, $request, $securityHeaders);

        $kernel->boot();
        $kernel->handle();

        exit;
    }

    // =================================================
    // CONTENEUR
    // =================================================

    private static function createContainer(): Container
    {
        $container = new Container();

        ContainerRegistry::set($container);

        $container->singleton(Request::class, static fn (): Request => Request::capture());

        $container->singleton(Database::class);

        return $container;
    }

    /**
     * @param (callable(Container): void)|null $serviceProvider
     */
    private static function registerServices(Container $container, ?callable $serviceProvider): void
    {
        if ($serviceProvider !== null)
        {
            $serviceProvider($container);
        }
    }

    // =================================================
    // ROUTEUR
    // =================================================

    private static function registerRoutes(Router $router): void
    {
        $routes = require base_path('Config/routes/web.php');

        if (! is_callable($routes))
        {
            throw new RuntimeException('Config/routes/web.php must return a callable.');
        }

        $routes($router);
    }

    // =================================================
    // MESURE DES PERFORMANCES
    // =================================================

    private static function startProfiler(int|float $startedAt): void
    {
        if (! ApplicationConfig::debug() || config('app.profiler', false) !== true)
        {
            return;
        }

        Profiler::startRequest($startedAt);

        register_shutdown_function(
            static function (): void
            {
                $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
                $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
                $status = http_response_code();

                Profiler::finishRequest(method: $method, uri: $uri, status: is_int($status) ? $status : 200);
            }
        );
    }

    // =================================================
    // CONFIGURATION
    // =================================================

    /**
     * @param (callable(int, string, Request): never)|null $renderer
     */
    private static function configureErrorHandler(?callable $renderer): void
    {
        if ($renderer !== null)
        {
            HttpErrorHandler::setRenderer($renderer);
        }

        HttpErrorHandler::register();
    }

    private static function configureDebug(): void
    {
        $debug = ApplicationConfig::debug();

        error_reporting(E_ALL);

        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
    }

    private static function configureTimezone(): void
    {
        $timezone = ApplicationConfig::timezone();

        if (! date_default_timezone_set($timezone))
        {
            throw new RuntimeException('Invalid application timezone: ' . $timezone);
        }
    }
}
