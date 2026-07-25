<?php

declare(strict_types=1);

return [
    'enabled' => env_bool('CACHE_ENABLED', false),
    'ttl' => max(1, env_int('CACHE_TTL', 300)),
];