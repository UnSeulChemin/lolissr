<?php

declare(strict_types=1);

use App\Autoloader;
use App\Core\Application\Bootstrap;

define(
    'ROOT',
    dirname(__DIR__),
);

require_once ROOT . '/Autoloader.php';

Autoloader::register();

require_once ROOT . '/Framework/Support/helpers.php';

Bootstrap::run();