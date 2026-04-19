<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Env;

/*
|--------------------------------------------------------------------------
| MASTER SWITCH TESTS
|--------------------------------------------------------------------------
|
| true  = suite de tests active
| false = aucun test exécuté
|
*/
$testsEnabled = (bool) Env::get('TESTS_ENABLED', true);

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
    'base' => rtrim('http://localhost' . App::basePath(), '/'),

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
        $testsEnabled && (bool) Env::get('TEST_CANONICAL_REDIRECT', true),

    'testPostAjouter' =>
        $testsEnabled && (bool) Env::get('TEST_POST_AJOUTER', false),

    'testPostUpdate' =>
        $testsEnabled && (bool) Env::get('TEST_POST_UPDATE', true),

    'testAjaxUpdate' =>
        $testsEnabled && (bool) Env::get('TEST_AJAX_UPDATE', false),

    'testUploadDuplicateSlugNumero' =>
        $testsEnabled && (bool) Env::get('TEST_UPLOAD_DUPLICATE_SLUG_NUMERO', true),

    'testUploadInvalidImage' =>
        $testsEnabled && (bool) Env::get('TEST_UPLOAD_INVALID_IMAGE', true),

    'testUploadMaxSize' =>
        $testsEnabled && (bool) Env::get('TEST_UPLOAD_MAX_SIZE', true),

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    'exportEnabled' => $testsEnabled,
];