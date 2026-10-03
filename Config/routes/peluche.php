<?php

declare(strict_types=1);

use App\Http\Controllers\Peluche\PelucheController;
use App\Http\Controllers\Peluche\PelucheAjaxController;
use App\Http\Routing\CollectionRouteRegistrar;
use Framework\Routing\Router;

/** @var Router $router */
CollectionRouteRegistrar::register(
    $router,
    'peluche',
    PelucheController::class,
    PelucheAjaxController::class,
    collectionPath: 'peluches'
);
