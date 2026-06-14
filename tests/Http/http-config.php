<?php

declare(strict_types=1);

return [

    'base' => rtrim(
        'http://localhost' . base_uri(),
        '/',
    ),

    'timeout' => 10,

    'user_agent' => 'LoliSSR-TestRunner',

    'username' => env(
        'HTTP_TEST_USERNAME',
        '',
    ),

    'password' => env(
        'HTTP_TEST_PASSWORD',
        '',
    ),

];