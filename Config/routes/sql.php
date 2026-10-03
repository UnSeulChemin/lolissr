<?php

declare(strict_types=1);

use App\Http\Controllers\Sql\SqlConsoleController;
use App\Http\Controllers\Sql\SqlQueryController;

use Framework\Config\ApplicationConfig;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

if (ApplicationConfig::isProduction() || ! env_bool('SQL_TOOL_ENABLED', false))
{
    return;
}

$router->prefix('sql')->group(function (Router $router): void
{
    // =================================================
    // PAGE
    // =================================================

    $router->get('', [SqlConsoleController::class, 'index']);

    // =================================================
    // EXÉCUTION HTML
    // =================================================

    $router->post('', [SqlConsoleController::class, 'execute'], [CsrfMiddleware::class]);

    // =================================================
    // EXÉCUTION JSON
    // =================================================

    $router
        ->prefix('ajax')
        ->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
        ->group(function (Router $router): void
        {
            $router->post('execute', [SqlQueryController::class, 'execute']);
        });
});