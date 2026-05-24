<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS 404 / PAGINATION
|--------------------------------------------------------------------------
*/

addGetTest($tests, [
    'category' => 'HTTP 404',
    'label' => 'Série inexistante',
    'path' => '/manga/serie/slug-inexistant-test-http',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'HTTP 404',
    'label' => 'Tome inexistant',
    'path' => '/manga/slug-inexistant-test-http/999999',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'HTTP 404',
    'label' => 'Page modifier inexistante',
    'path' => '/manga/update/slug-inexistant-test-http/999999',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'HTTP 404',
    'label' => 'Pagination très élevée',
    'path' => '/manga/collection/page/999',
    'expected_status' => 404,
]);