<?php

declare(strict_types=1);

use App\Controllers\Sql\SqlAjaxController;
use App\Controllers\Sql\SqlController;

use Framework\Application\App;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

if (App::isProduction())
{
    return;
}

$router->prefix('sql')->group(function (Router $router): void
{
    $router->get('', [SqlController::class, 'index']);

    $router->post('', [SqlController::class, 'execute'], [CsrfMiddleware::class]);

    $router->prefix('ajax')
        ->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
        ->group(function (Router $router): void
        {
            $router->post('execute', [SqlAjaxController::class, 'execute']);
        });
});