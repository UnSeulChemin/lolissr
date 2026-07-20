<?php

declare(strict_types=1);

use App\Controllers\Auth\AuthController;
use App\Controllers\MainController;

use Framework\Http\Middleware\AuthMiddleware;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Routing\Router;

return static function (Router $router): void
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/routes/auth.php';

    /*
    |--------------------------------------------------------------------------
    | PROTECTED
    |--------------------------------------------------------------------------
    */

    $router->middleware(AuthMiddleware::class)
        ->group(function (Router $router): void
    {
        $router->get('/', [MainController::class, 'index']);

        $router->post('deconnexion',
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