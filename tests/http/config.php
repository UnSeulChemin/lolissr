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
| DIRECTORIES
|--------------------------------------------------------------------------
*/
$httpRoot = ROOT . '/tests/Http';

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
    | PATHS
    |--------------------------------------------------------------------------
    */
    'httpRoot' => $httpRoot,
    'casesDirectory' => $httpRoot . '/cases',
    'fixturesDirectory' => $httpRoot . '/fixtures',
    'tmpUploadsDirectory' => $httpRoot . '/tmp-uploads',
    'exportDirectory' => $httpRoot . '/reports',

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

    'testUploadDuplicateSlugNumero' =>
        $testsEnabled && (bool) Functions::env('TEST_UPLOAD_DUPLICATE_SLUG_NUMERO', true),

    'testUploadInvalidImage' =>
        $testsEnabled && (bool) Functions::env('TEST_UPLOAD_INVALID_IMAGE', true),

    'testUploadMaxSize' =>
        $testsEnabled && (bool) Functions::env('TEST_UPLOAD_MAX_SIZE', true),

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    'exportEnabled' => $testsEnabled,
];