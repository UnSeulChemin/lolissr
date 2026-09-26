<?php

declare(strict_types=1);

use App\Controllers\Figurine\FigurineController;
use App\Controllers\Figurine\FigurineAjaxController;
use App\Support\CollectionRoutes;
use Framework\Routing\Router;

/** @var Router $router */
CollectionRoutes::register(
    $router,
    'figurine',
    FigurineController::class,
    FigurineAjaxController::class,
    withLinks: true
);
