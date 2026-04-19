<?php

declare(strict_types=1);

use App\Core\Config\Env;

return [

    'enabled' => (bool) Env::get('TESTS_ENABLED', true),

    'post_ajouter' => (bool) Env::get('TEST_POST_AJOUTER', false),
    'post_update' => (bool) Env::get('TEST_POST_UPDATE', false),

    'upload_mode' => (bool) Env::get('TEST_UPLOAD_MODE', true),
    'upload_real' => (bool) Env::get('TEST_UPLOAD_REAL', false),
    'upload_dir' => (string) Env::get('TEST_UPLOAD_DIR', 'tests/Http/tmp-uploads'),

    'ajax_json' => (bool) Env::get('TEST_AJAX_JSON', true),
    'pagination' => (bool) Env::get('TEST_PAGINATION', true),
    'errors' => (bool) Env::get('TEST_ERRORS', true),
    'ajax_update' => (bool) Env::get('TEST_AJAX_UPDATE', false),

];