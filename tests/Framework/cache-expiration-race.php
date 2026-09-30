<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';
use Framework\Cache\Cache;
$directory = sys_get_temp_dir() . '/cache-race-' . bin2hex(random_bytes(8));
mkdir($directory);
$property = new ReflectionProperty(Cache::class, 'directory');
$previous = $property->getValue();
$property->setValue(null, $directory);
$delete = new ReflectionMethod(Cache::class, 'deleteObservedEntry');
$path = $directory . '/fixture.cache';
try
{
    foreach (['{"expires_at":1,"value":"old"}', '{broken'] as $observed)
    {
        file_put_contents($path, $observed);
        // Deterministic interleaving: a second request publishes after the first read.
        $fresh = json_encode(['expires_at' => time() + 300, 'value' => 'fresh'], JSON_THROW_ON_ERROR);
        file_put_contents($path, $fresh);
        $delete->invoke(null, $path, $observed);
        if (file_get_contents($path) !== $fresh) throw new RuntimeException('Fresh cache removed by stale reader.');
        file_put_contents($path, $observed);
        $delete->invoke(null, $path, $observed);
        if (is_file($path)) throw new RuntimeException('Unchanged invalid cache not removed.');
    }
    echo "PASS: expired/corrupt stale readers preserve refreshed cache; unchanged entries removed.\n";
}
finally
{
    $property->setValue(null, $previous);
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($directory . '/.metadata.lock')) unlink($directory . '/.metadata.lock');
    rmdir($directory);
}
