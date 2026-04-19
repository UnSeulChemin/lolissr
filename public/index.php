<?php

declare(strict_types=1);

use App\Autoloader;
use App\Core\Bootstrap;

session_start();

define('ROOT', dirname(__DIR__));

require_once ROOT . '/Autoloader.php';

Autoloader::register();

Bootstrap::run();