<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;

use Framework\Http\Middleware\AuthMiddleware;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Routing\Router;

return static function (Router $router): void
{
    // =================================================
    // ROUTES PUBLIQUES
    // =================================================

    require __DIR__ . '/routes/auth.php';

    // =================================================
    // ROUTES PROTÉGÉES
    // =================================================

    $router
        ->middleware(AuthMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get('', [HomeController::class, 'index']);
            $router->get('recherche', [\App\Http\Controllers\GlobalSearchController::class, 'search'],
                [\Framework\Http\Middleware\ExpectJsonMiddleware::class]);

            $router->post(
                'deconnexion',
                [AuthController::class, 'logout'],
                [CsrfMiddleware::class]
            );

            require __DIR__ . '/routes/profile.php';
            require __DIR__ . '/routes/sql.php';
            require __DIR__ . '/routes/manga.php';
            require __DIR__ . '/routes/figurine.php';
            require __DIR__ . '/routes/nendoroid.php';
            require __DIR__ . '/routes/peluche.php';
            require __DIR__ . '/routes/chinois.php';
        });
};
