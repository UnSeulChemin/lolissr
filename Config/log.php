<?php

declare(strict_types=1);

return [

    'enabled' => env_bool('LOG_ENABLED', true),

    'retention_days' => max(
        1,
        env_int('LOG_RETENTION_DAYS', 14)
    ),

];