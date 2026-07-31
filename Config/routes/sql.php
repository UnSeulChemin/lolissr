<?php

declare(strict_types=1);

use App\Controllers\Sql\SqlAjaxController;
use App\Controllers\Sql\SqlController;

use Framework\Application\App;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

if (App::isProduction() || ! env_bool('SQL_TOOL_ENABLED', false))
{
    return;
}

$router->prefix('sql')->group(function (Router $router): void
{
    // =========================================
    // PAGE
    // =========================================

    $router->get('', [SqlController::class, 'index']);

    // =========================================
    // EXÉCUTION HTML
    // =========================================

    $router->post(
        '',
        [SqlController::class, 'execute'],
        [CsrfMiddleware::class]
    );

    // =========================================
    // EXÉCUTION JSON
    // =========================================

    $router
        ->prefix('ajax')
        ->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
        ->group(function (Router $router): void
        {
            $router->post('execute', [SqlAjaxController::class, 'execute']);
        });
});