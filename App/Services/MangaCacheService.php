<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache\Cache;

final class MangaCacheService
{
    public function clear(): void
    {
        Cache::clear();
    }
}