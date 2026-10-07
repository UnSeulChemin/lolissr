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
        $router->post('commandes/js-prune', [AdminController::class, 'pruneJavaScript'], [CsrfMiddleware::class]);
        $router->post('commandes/js-prune-force', [AdminController::class, 'forcePruneJavaScript'], [CsrfMiddleware::class]);
        $router->post('commandes/images-check', [AdminController::class, 'checkImages'], [CsrfMiddleware::class]);
        $router->post('commandes/images', [AdminController::class, 'images'], [CsrfMiddleware::class]);
        $router->post('commandes/cache', [AdminController::class, 'clearCache'], [CsrfMiddleware::class]);
        $router->post('commandes/migrations-check', [AdminController::class, 'checkMigrations'], [CsrfMiddleware::class]);
        $router->post('commandes/migrations-create', [AdminController::class, 'createMigration'], [CsrfMiddleware::class]);
        $router->post('commandes/migrations', [AdminController::class, 'applyMigrations'], [CsrfMiddleware::class]);
        $router->post('commandes/backup', [AdminController::class, 'backupDatabase'], [CsrfMiddleware::class]);
        $router->post('commandes/xp-check', [AdminController::class, 'checkXp'], [CsrfMiddleware::class]);
        $router->post('commandes/xp-check/mon-compte', [AdminController::class, 'checkMyXp'], [CsrfMiddleware::class]);
        $router->post('commandes/xp-apply', [AdminController::class, 'applyXp'], [CsrfMiddleware::class]);
        $router->post('commandes/xp-apply/mon-compte', [AdminController::class, 'applyMyXp'], [CsrfMiddleware::class]);
        $router->post('commandes/reset', [AdminController::class, 'resetDev'], [CsrfMiddleware::class]);
    });
});
