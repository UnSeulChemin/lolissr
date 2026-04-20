<?php

declare(strict_types=1);

use App\Core\Application\App;
use App\Core\Config\Env;

/*
|--------------------------------------------------------------------------
| MASTER SWITCH TESTS
|--------------------------------------------------------------------------
|
| true  = suite de tests active
| false = aucun test exécuté
|
*/
$testsEnabled = Env::bool('TESTS_ENABLED', true);

/*
|--------------------------------------------------------------------------
| ENVIRONNEMENT TEST
|--------------------------------------------------------------------------
|
| Les tests mutateurs ne doivent tourner que si l'application
| est explicitement en environnement "testing".
|
*/
$isTestingAppEnv = App::isTesting();

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
    'tmpUploadsDirectory' => ROOT . '/'
        . trim((string) Env::get('TEST_UPLOAD_DIR', 'tests/Http/tmp-uploads'), '/'),
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
    | INFOS ENV
    |--------------------------------------------------------------------------
    */
    'testsEnabled' => $testsEnabled,
    'isTestingAppEnv' => $isTestingAppEnv,

    /*
    |--------------------------------------------------------------------------
    | OPTIONS TESTS
    |--------------------------------------------------------------------------
    */
    'testCanonicalRedirect' =>
        $testsEnabled && Env::bool('TEST_CANONICAL_REDIRECT', true),

    'testPostAjouter' =>
        $testsEnabled
        && $isTestingAppEnv
        && Env::bool('TEST_POST_AJOUTER', false),

    'testPostUpdate' =>
        $testsEnabled
        && $isTestingAppEnv
        && Env::bool('TEST_POST_UPDATE', true),

    'testAjaxUpdate' =>
        $testsEnabled
        && $isTestingAppEnv
        && Env::bool('TEST_AJAX_UPDATE', false),

    'testUploadDuplicateSlugNumero' =>
        $testsEnabled
        && $isTestingAppEnv
        && Env::bool('TEST_UPLOAD_DUPLICATE_SLUG_NUMERO', true),

    'testUploadInvalidImage' =>
        $testsEnabled
        && $isTestingAppEnv
        && Env::bool('TEST_UPLOAD_INVALID_IMAGE', true),

    'testUploadMaxSize' =>
        $testsEnabled
        && $isTestingAppEnv
        && Env::bool('TEST_UPLOAD_MAX_SIZE', true),

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    'exportEnabled' => $testsEnabled,
];