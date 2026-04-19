<?php

declare(strict_types=1);

use App\Core\Functions;

return [

    'enabled' => (bool) Functions::env('CACHE_ENABLED', false),

];