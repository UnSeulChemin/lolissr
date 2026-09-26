<?php

declare(strict_types=1);

use App\Controllers\Nendoroid\NendoroidController;
use App\Controllers\Nendoroid\NendoroidAjaxController;
use App\Support\CollectionRoutes;
use Framework\Routing\Router;

/** @var Router $router */
CollectionRoutes::register(
    $router,
    'nendoroid',
    NendoroidController::class,
    NendoroidAjaxController::class
);
