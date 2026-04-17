<?php

declare(strict_types=1);

use App\Core\Functions;

/*
|--------------------------------------------------------------------------
| MASTER SWITCH TESTS
|--------------------------------------------------------------------------
|
| true  = suite de tests active
| false = aucun test exécuté
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
    'base' => rtrim('http://localhost' . Functions::basePath(), '/'),

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
    'exportEnabled' => $testsEnabled,
];