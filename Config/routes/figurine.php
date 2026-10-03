<?php

declare(strict_types=1);

use App\Http\Controllers\Figurine\FigurineAjaxController;
use App\Http\Controllers\Figurine\FigurineController;
use App\Http\Routing\CollectionRouteRegistrar;

use Framework\Routing\Router;

/** @var Router $router */
CollectionRouteRegistrar::register(
    $router,
    'figurine',
    FigurineController::class,
    FigurineAjaxController::class,
    withLinks: true,
    collectionPath: 'figurines'
);
