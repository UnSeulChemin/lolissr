<?php

declare(strict_types=1);

use App\Core\Config\Env;

return [

    'enabled' => (bool) Env::get('CACHE_ENABLED', false),

];