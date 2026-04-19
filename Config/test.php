<?php

declare(strict_types=1);

use App\Core\Functions;

return [

    'enabled' => (bool) Functions::env('TESTS_ENABLED', true),

    'post_ajouter' => (bool) Functions::env('TEST_POST_AJOUTER', false),
    'post_update' => (bool) Functions::env('TEST_POST_UPDATE', false),

    'upload_mode' => (bool) Functions::env('TEST_UPLOAD_MODE', true),
    'upload_real' => (bool) Functions::env('TEST_UPLOAD_REAL', false),
    'upload_dir' => (string) Functions::env('TEST_UPLOAD_DIR', 'tests/Http/tmp-uploads'),

    'ajax_json' => (bool) Functions::env('TEST_AJAX_JSON', true),
    'pagination' => (bool) Functions::env('TEST_PAGINATION', true),
    'errors' => (bool) Functions::env('TEST_ERRORS', true),
    'ajax_update' => (bool) Functions::env('TEST_AJAX_UPDATE', false),

];