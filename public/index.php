<?php
define('ROOT', dirname(__DIR__));

use App\Autoloader;
use App\Core\Main;

require_once ROOT.'/Autoloader.php';
Autoloader::register();

$app = new Main();
$app->start();