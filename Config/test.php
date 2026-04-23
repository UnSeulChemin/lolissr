<?php

declare(strict_types=1);

return [

    'enabled' => env_bool('TESTS_ENABLED', true),

    'post_ajouter' => env_bool('TEST_POST_AJOUTER', false),
    'post_update' => env_bool('TEST_POST_UPDATE', false),

    'upload_mode' => env_bool('TEST_UPLOAD_MODE', true),
    'upload_real' => env_bool('TEST_UPLOAD_REAL', false),
    'upload_dir' => env('TEST_UPLOAD_DIR', 'tests/Http/tmp-uploads'),

    'ajax_json' => env_bool('TEST_AJAX_JSON', true),
    'pagination' => env_bool('TEST_PAGINATION', true),
    'errors' => env_bool('TEST_ERRORS', true),
    'ajax_update' => env_bool('TEST_AJAX_UPDATE', false),

];