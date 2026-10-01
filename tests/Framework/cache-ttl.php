<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use Framework\Cache\Cache;
use Framework\Config\Config;

$directory = sys_get_temp_dir() . '/cache-ttl-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$property = new ReflectionProperty(Cache::class, 'directory');
$previous = $property->getValue();
$property->setValue(null, $directory);
Config::prime(['cache' => ['enabled' => true, 'ttl' => PHP_INT_MAX]]);
try
{
    foreach (['configured' => null, 'explicit' => PHP_INT_MAX, 'ordinary' => 60] as $key => $ttl)
    {
        $calls = 0;
        $compute = static function () use (&$calls): string { $calls++; return 'cached'; };
        $before = time();
        Cache::remember($key, $ttl, $compute);
        $after = time();
        if (Cache::remember($key, $ttl, $compute) !== 'cached' || $calls !== 1)
            throw new RuntimeException('Cache immediately expired: ' . $key);

        $path = (new ReflectionMethod(Cache::class, 'path'))->invoke(null, $key);
        $payload = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $expiresAt = $payload['expires_at'];
        if (!is_int($expiresAt)) throw new RuntimeException('Expiration is not an integer.');
        if ($key === 'ordinary')
        {
            if ($expiresAt < $before + 60 || $expiresAt > $after + 60)
                throw new RuntimeException('Ordinary TTL changed.');
        }
        elseif ($expiresAt !== PHP_INT_MAX) throw new RuntimeException('Extreme TTL did not saturate.');

        Cache::forget($key);
        Cache::remember($key, $ttl, $compute);
        if ($calls !== 2) throw new RuntimeException('Cache invalidation failed: ' . $key);
    }
}
finally
{
    Config::clear();
    $property->setValue(null, $previous);
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($directory . '/.metadata.lock')) unlink($directory . '/.metadata.lock');
    rmdir($directory);
}
echo "PASS: configured and explicit extreme TTLs, integer expiration, ordinary TTL and invalidation.\n";
