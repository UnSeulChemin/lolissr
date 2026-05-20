<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

if (!defined('ROOT'))
{
    define(
        'ROOT',
        dirname(__DIR__),
    );
}

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/Framework/Support/helpers.php';

Bootstrap::run();