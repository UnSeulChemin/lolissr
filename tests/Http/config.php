<?php

declare(strict_types=1);

use Framework\Application\App;

/*
|--------------------------------------------------------------------------
| MASTER SWITCH TESTS
|--------------------------------------------------------------------------
|
| true  = suite de tests active
| false = aucun test exécuté
|
*/

$testsEnabled = env_bool(
    'TESTS_ENABLED',
    true,
);

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

$httpRoot = base_path(
    'tests/Http',
);

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

    'base' => rtrim(
        'http://localhost',
        '/',
    ),

    /*
    |--------------------------------------------------------------------------
    | PATHS
    |--------------------------------------------------------------------------
    */

    'httpRoot' => $httpRoot,

    'casesDirectory' => base_path(
        'tests/Http/cases',
    ),

    'fixturesDirectory' => base_path(
        'tests/Http/fixtures',
    ),

    'tmpUploadsDirectory' => base_path(
        trim(
            (string) env(
                'TEST_UPLOAD_DIR',
                'tests/Http/tmp-uploads',
            ),
            '/',
        ),
    ),

    'exportDirectory' => base_path(
        'tests/Http/reports',
    ),

    /*
    |--------------------------------------------------------------------------
    | CONFIG DONNÉES
    |--------------------------------------------------------------------------
    */

    'realSlug' => 'i-want-to-see-you-shy',
    'realNumero' => 1,
    'nonCanonicalSlug' => 'I-Want-To-See-You-Shy',

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
        $testsEnabled
        && env_bool(
            'TEST_CANONICAL_REDIRECT',
            true,
        ),

    'testPostAjouter' =>
        $testsEnabled
        && $isTestingAppEnv
        && env_bool(
            'TEST_POST_AJOUTER',
            false,
        ),

    'testPostUpdate' =>
        $testsEnabled
        && $isTestingAppEnv
        && env_bool(
            'TEST_POST_UPDATE',
            true,
        ),

    'testAjaxUpdate' =>
        $testsEnabled
        && $isTestingAppEnv
        && env_bool(
            'TEST_AJAX_UPDATE',
            false,
        ),

    'testUploadDuplicateSlugNumero' =>
        $testsEnabled
        && $isTestingAppEnv
        && env_bool(
            'TEST_UPLOAD_DUPLICATE_SLUG_NUMERO',
            true,
        ),

    'testUploadInvalidImage' =>
        $testsEnabled
        && $isTestingAppEnv
        && env_bool(
            'TEST_UPLOAD_INVALID_IMAGE',
            true,
        ),

    'testUploadMaxSize' =>
        $testsEnabled
        && $isTestingAppEnv
        && env_bool(
            'TEST_UPLOAD_MAX_SIZE',
            true,
        ),

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    'exportEnabled' => $testsEnabled,
];