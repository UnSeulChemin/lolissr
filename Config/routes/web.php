<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Home\HomeController;

use Framework\Http\Middleware\AuthMiddleware;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Routing\Router;

return static function (Router $router): void
{
    // =================================================
    // ROUTES PUBLIQUES
    // =================================================

    require __DIR__ . '/auth.php';

    // =================================================
    // ROUTES PROTÉGÉES
    // =================================================

    $router
        ->middleware(AuthMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get('', [HomeController::class, 'index']);
            $router->get('recherche', [\App\Http\Controllers\Search\GlobalSearchController::class, 'search'],
                [\Framework\Http\Middleware\ExpectJsonMiddleware::class]);

            $router->post('deconnexion', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

            require __DIR__ . '/profile.php';
            require __DIR__ . '/sql.php';
            require __DIR__ . '/manga.php';
            require __DIR__ . '/figurine.php';
            require __DIR__ . '/nendoroid.php';
            require __DIR__ . '/peluche.php';
            require __DIR__ . '/chinois.php';
        });
};
