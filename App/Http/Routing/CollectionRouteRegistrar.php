<?php

declare(strict_types=1);

namespace App\Http\Routing;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

final class CollectionRouteRegistrar
{
    /**
     * Enregistrer les routes communes des collections de figurines, nendoroids et peluches.
     * Chaque route hérite du filtre d’authentification de l’appelant.
     *
     * @param class-string $controller
     * @param class-string $ajaxController
     */
    public static function register(
        Router $router,
        string $prefix,
        string $controller,
        string $ajaxController,
        bool $withLinks = false,
        string $collectionPath = 'waifus'
    ): void
    {
        $router->prefix($prefix)->group(function (Router $router) use ($controller, $ajaxController, $withLinks, $collectionPath): void
        {
            // =================================================
            // INDEX
            // =================================================

            $router->get('', [$controller, 'index']);
            if ($withLinks)
            {
                $router->get('lien', [$controller, 'links']);
            }

            // =================================================
            // WAIFUS
            // =================================================

            $router->prefix($collectionPath)->group(function (Router $router) use ($controller, $ajaxController): void
            {
                $router->get('', [$controller, 'waifus']);
                $router->get('page/{page:int}', [$controller, 'waifus']);

                // =================================================
                // MODIFICATION
                // =================================================

                $router->get('{slug}/modifier/{numero:int}', [$controller, 'edit']);

                $router->post('{slug}/modifier/{numero:int}', [$controller, 'update'], [CsrfMiddleware::class]);

                // =================================================
                // SUPPRESSION
                // =================================================

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [$ajaxController, 'delete'],
                    [ExpectJsonMiddleware::class, CsrfMiddleware::class]
                );

                // =================================================
                // CONSULTATION
                // =================================================

                $router->get('{slug}/{numero:int}', [$controller, 'showWaifu']);
            });

            // =================================================
            // AJOUT
            // =================================================

            $router->get('ajouter', [$controller, 'create']);

            $router->post('ajouter', [$controller, 'store'], [CsrfMiddleware::class]);

            // =================================================
            // AJAX
            // =================================================

            $router->prefix('ajax')->group(function (Router $router) use ($ajaxController, $collectionPath): void
            {
                // =================================================
                // HTML
                // =================================================

                $router->get($collectionPath . '/page/{page:int}', [$ajaxController, 'waifusPage']);

                // =================================================
                // JSON
                // =================================================

                $router
                    ->middleware(ExpectJsonMiddleware::class)
                    ->group(function (Router $router) use ($ajaxController): void
                    {
                        $router->get('recherche/{query}', [$ajaxController, 'search']);

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
