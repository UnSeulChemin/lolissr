<?php

declare(strict_types=1);

use App\Core\Functions;

/*
|--------------------------------------------------------------------------
| MASTER SWITCH TESTS
|--------------------------------------------------------------------------
|
| Permet de désactiver toute la suite avec :
| TESTS_ENABLED=false dans .env
|
*/

$testsEnabled = (bool) Functions::env('TESTS_ENABLED', true);

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | BASE URL
    |--------------------------------------------------------------------------
    */

    'base' => 'http://localhost/lolissr/',

    /*
    |--------------------------------------------------------------------------
    | CONFIG DONNÉES
    |--------------------------------------------------------------------------
    */

    'realSlug' => 'one-piece',
    'realNumero' => 1,
    'nonCanonicalSlug' => 'One-Piece',

    /*
    |--------------------------------------------------------------------------
    | OPTIONS TESTS
    |--------------------------------------------------------------------------
    |
    | Chaque test dépend de TESTS_ENABLED
    | Si TESTS_ENABLED=false → tout OFF
    |
    */

    'testCanonicalRedirect' =>
        $testsEnabled && (bool) Functions::env('TEST_CANONICAL_REDIRECT', true),

    'testPostAjouter' =>
        $testsEnabled && (bool) Functions::env('TEST_POST_AJOUTER', false),

    'testPostUpdate' =>
        $testsEnabled && (bool) Functions::env('TEST_POST_UPDATE', true),

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    'exportDirectory' => __DIR__ . '/reports',

    'exportEnabled' =>
        $testsEnabled && true,
];