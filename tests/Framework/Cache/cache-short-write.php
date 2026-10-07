<?php

declare(strict_types=1);

namespace Framework\Cache;

// Simuler un disque qui accepte seulement une partie du JSON temporaire.
function file_put_contents(string $path, mixed $contents, int $flags = 0): int|false
{
    return \file_put_contents($path, str_ends_with($path, '.tmp') ? substr($contents, 0, 3) : $contents, $flags);
}

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use Framework\Config\Config;

$directory = sys_get_temp_dir() . '/cache-short-write-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$property = new \ReflectionProperty(Cache::class, 'directory');
$previous = $property->getValue();
$property->setValue(null, $directory);
Config::prime(['cache' => ['enabled' => true, 'ttl' => 60]]);

try
{
    $calls = 0;
    $compute = static function () use (&$calls): string
    { $calls++; return 'complete value'; };
    for ($attempt = 0; $attempt < 2; $attempt++)
    {
        if (Cache::remember('short-write', 60, $compute) !== 'complete value')
            throw new \RuntimeException('A failed cache write changed the response.');
        if (glob($directory . '/*.cache') !== [] || glob($directory . '/*.tmp') !== [])
            throw new \RuntimeException('Partial cache data was published or left staged.');
    }
    if ($calls !== 2) throw new \RuntimeException('A failed write became a cache hit.');
}
finally
{
    Config::clear();
    $property->setValue(null, $previous);
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}

echo "PASS: short cache writes preserve responses, publish no partial JSON and clean staging files.\n";
