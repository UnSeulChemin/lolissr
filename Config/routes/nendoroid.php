<?php

declare(strict_types=1);

use App\Http\Controllers\Nendoroid\NendoroidController;
use App\Http\Controllers\Nendoroid\NendoroidAjaxController;
use App\Http\Routing\CollectionRouteRegistrar;
use Framework\Routing\Router;

/** @var Router $router */
CollectionRouteRegistrar::register(
    $router,
    'nendoroid',
    NendoroidController::class,
    NendoroidAjaxController::class,
    collectionPath: 'nendoroids'
);
