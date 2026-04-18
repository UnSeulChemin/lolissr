<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| EDGE CASES
|--------------------------------------------------------------------------
*/

addGetTest($tests, [
    'category' => 'Edge',
    'label' => 'Slug vide',
    'path' => '/manga//1',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Edge',
    'label' => 'Numero négatif',
    'path' => '/manga/' . $realSlug . '/-1',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Edge',
    'label' => 'Numero texte',
    'path' => '/manga/' . $realSlug . '/abc',
    'expected_status' => 404,
]);

addGetTest($tests, [
    'category' => 'Edge',
    'label' => 'Route inconnue totale',
    'path' => '/route-totalement-inconnue-test',
    'expected_status' => 404,
]);