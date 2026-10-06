<?php
declare(strict_types=1);

use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\AdminOwnerMiddleware;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Routing\Router;

/** @var Router $router */
$router->prefix('admin')->middleware(AdminOwnerMiddleware::class)->group(function (Router $router): void
{
    $router->get('', [AdminController::class, 'index']);
    $router->get('commandes', [AdminController::class, 'commands']);
    $router->get('commandes/etat', [AdminController::class, 'jobStatus']);
    $router->post('commandes/sorties', [AdminController::class, 'releases'], [CsrfMiddleware::class]);
    $router->post('commandes/sorties/mon-compte', [AdminController::class, 'myReleases'], [CsrfMiddleware::class]);
    $router->post('commandes/recommandations', [AdminController::class, 'recommendations'], [CsrfMiddleware::class]);
    $router->post('commandes/recommandations/mon-compte', [AdminController::class, 'myRecommendations'], [CsrfMiddleware::class]);
    $router->prefix('dev')->group(function (Router $router): void
    {
        $router->get('', [AdminController::class, 'dev']);
        $router->get('commandes', [AdminController::class, 'devCommands']);
        $router->post('commandes/doctor', [AdminController::class, 'doctor'], [CsrfMiddleware::class]);
        $router->post('commandes/assets', [AdminController::class, 'buildAssets'], [CsrfMiddleware::class]);
        $router->post('commandes/images-check', [AdminController::class, 'checkImages'], [CsrfMiddleware::class]);
        $router->post('commandes/images', [AdminController::class, 'images'], [CsrfMiddleware::class]);
        $router->post('commandes/cache', [AdminController::class, 'clearCache'], [CsrfMiddleware::class]);
        $router->post('commandes/reset', [AdminController::class, 'resetDev'], [CsrfMiddleware::class]);
    });
    require __DIR__ . '/sql.php';
});
