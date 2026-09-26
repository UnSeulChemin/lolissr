<?php

declare(strict_types=1);

namespace App\Support;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

final class CollectionRoutes
{
    /**
     * Register the shared figurine, nendoroid and plush collection routes.
     * The caller's authentication middleware is inherited by every route.
     *
     * @param class-string $controller
     * @param class-string $ajaxController
     */
    public static function register(
        Router $router,
        string $prefix,
        string $controller,
        string $ajaxController,
        bool $withLinks = false
    ): void {
        $router->prefix($prefix)->group(function (Router $router) use ($controller, $ajaxController, $withLinks): void
        {
            // =========================================
            // INDEX
            // =========================================

            $router->get('', [$controller, 'index']);
            if ($withLinks)
            {
                $router->get('lien', [$controller, 'links']);
            }

            // =========================================
            // WAIFUS
            // =========================================

            $router->prefix('waifus')->group(function (Router $router) use ($controller, $ajaxController): void
            {
                $router->get('', [$controller, 'waifus']);
                $router->get('page/{page:int}', [$controller, 'waifus']);

                // =========================================
                // MODIFICATION
                // =========================================

                $router->get(
                    '{slug}/modifier/{numero:int}',
                    [$controller, 'edit']
                );

                $router->post(
                    '{slug}/modifier/{numero:int}',
                    [$controller, 'update'],
                    [CsrfMiddleware::class]
                );

                // =========================================
                // SUPPRESSION
                // =========================================

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [$ajaxController, 'delete'],
                    [ExpectJsonMiddleware::class, CsrfMiddleware::class]
                );

                // =========================================
                // CONSULTATION
                // =========================================

                $router->get(
                    '{slug}/{numero:int}',
                    [$controller, 'showWaifu']
                );
            });

            // =========================================
            // AJOUT
            // =========================================

            $router->get('ajouter', [$controller, 'create']);

            $router->post(
                'ajouter',
                [$controller, 'store'],
                [CsrfMiddleware::class]
            );

            // =========================================
            // AJAX
            // =========================================

            $router->prefix('ajax')->group(function (Router $router) use ($ajaxController): void
            {
                // =========================================
                // HTML
                // =========================================

                $router->get(
                    'waifus/page/{page:int}',
                    [$ajaxController, 'waifusPage']
                );

                // =========================================
                // JSON
                // =========================================

                $router
                    ->middleware(ExpectJsonMiddleware::class)
                    ->group(function (Router $router) use ($ajaxController): void
                    {
                        $router->get(
                            'recherche/{query}',
                            [$ajaxController, 'search']
                        );

                        $router
                            ->middleware(CsrfMiddleware::class)
                            ->group(function (Router $router) use ($ajaxController): void
                            {
                                $router->post(
                                    'update-collect-status/{slug}/{numero:int}',
                                    [$ajaxController, 'updateCollectStatus']
                                );
                            });
                    });
            });
        });
    }
}
