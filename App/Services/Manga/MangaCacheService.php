<?php

declare(strict_types=1);

namespace App\Services\Manga;

use Framework\Cache\Cache;

final class MangaCacheService
{
    public function clear(): void
    {
        Cache::clear();
    }
}
