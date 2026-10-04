<?php

declare(strict_types=1);

namespace App\Cache;

final class CacheKey
{
    public const HOME_DASHBOARD = 'home.dashboard';

    public static function dashboard(?int $userId = null): string
    {
        $user = function_exists('user') ? user() : null;
        $userId ??= $user === null ? 0 : $user->id;
        return self::HOME_DASHBOARD . '.v2.user.' . $userId;
    }

    private function __construct()
    {
    }
}
