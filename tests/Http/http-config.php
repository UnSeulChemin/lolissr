<?php

declare(strict_types=1);

return [

    // =========================================
    // HTTP
    // =========================================

    'base' => rtrim('http://localhost' . base_uri(), '/'),
    'timeout' => 10,
    'user_agent' => 'LoliSSR-TestRunner',

    // =========================================
    // AUTHENTIFICATION
    // =========================================

    'username' => (string) env('HTTP_TEST_USERNAME', ''),
    'password' => (string) env('HTTP_TEST_PASSWORD', '')

];