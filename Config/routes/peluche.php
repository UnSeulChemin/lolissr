<?php

declare(strict_types=1);

use App\Controllers\Peluche\PelucheController;
use App\Controllers\Peluche\PelucheAjaxController;
use App\Support\CollectionRoutes;
use Framework\Routing\Router;

/** @var Router $router */
CollectionRoutes::register(
    $router,
    'peluche',
    PelucheController::class,
    PelucheAjaxController::class
);
