<?php

declare(strict_types=1);
require dirname(__DIR__) . '/scripts/lib/AtomicFile.php';
$directory = sys_get_temp_dir() . '/lolissr-atomic-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$path = $directory . '/manifest.php';
try
{
    AtomicFile::writeIfChanged($path, "<?php return ['version' => 1];\n");
    $expected = "<?php return ['version' => 2];\n";
    AtomicFile::writeIfChanged($path, $expected);
    if ((require $path)['version'] !== 2) throw new RuntimeException('Replacement failed');
    touch($path, 1000000000);
    AtomicFile::writeIfChanged($path, $expected);
    if (filemtime($path) !== 1000000000) throw new RuntimeException('Identical manifest rewritten');
    // Failed publication must preserve the destination and clean staging files.
    $blocked = $directory . '/blocked';
    mkdir($blocked);
    file_put_contents($blocked . '/sentinel', 'keep');
    try
    {
        AtomicFile::writeIfChanged($blocked, 'replacement');
        throw new RuntimeException('Unexpected replacement of a directory');
    }
    catch (RuntimeException $error)
    {
        if (!str_starts_with($error->getMessage(), 'Cannot replace output atomically:')) throw $error;
    }
    if (file_get_contents($blocked . '/sentinel') !== 'keep' || glob($directory . '/.build-*') !== [])
        throw new RuntimeException('Failed publication damaged destination or left staging files');
    echo "PASS: atomic replacement, unchanged file reuse and safe failure cleanup.\n";
}
finally
{
    if (is_file($path)) unlink($path);
    if (is_file($directory . '/blocked/sentinel')) unlink($directory . '/blocked/sentinel');
    if (is_dir($directory . '/blocked')) rmdir($directory . '/blocked');
    rmdir($directory);
}
