<?php
declare(strict_types=1);
require dirname(__DIR__, 3) . '/scripts/Release/Support/ReleaseArchive.php';
$root = sys_get_temp_dir() . '/release-archive-' . bin2hex(random_bytes(8));
mkdir($root . '/source', 0755, true);
try
{
    file_put_contents($root . '/source/app.txt', 'version one');
    ReleaseArchive::create($root . '/source', $root . '/release.zip');
    $original = hash_file('sha256', $root . '/release.zip');
    try
    { ReleaseArchive::create($root . '/missing', $root . '/release.zip'); }
    catch (Throwable)
    { /* Expected: source cannot be read. */ }
    if (hash_file('sha256', $root . '/release.zip') !== $original) throw new RuntimeException('Previous release lost on failure.');
    file_put_contents($root . '/source/app.txt', 'version two');
    ReleaseArchive::create($root . '/source', $root . '/release.zip');
    $zip = new ZipArchive();
    $zip->open($root . '/release.zip', ZipArchive::CHECKCONS);
    $contents = $zip->getFromName('app.txt');
    $zip->close();
    if ($contents !== 'version two' || glob($root . '/.release-*') !== []) throw new RuntimeException('Publication or cleanup failed.');
    mkdir($root . '/blocked.zip');
    file_put_contents($root . '/blocked.zip/keep', 'preserved');
    $failed = false;
    try
    { ReleaseArchive::create($root . '/source', $root . '/blocked.zip'); }
    catch (RuntimeException)
    { $failed = true; }
    if (!$failed || file_get_contents($root . '/blocked.zip/keep') !== 'preserved' || glob($root . '/.release-*') !== [])
        throw new RuntimeException('Failed replacement did not preserve destination or clean staging.');
    echo "PASS: verified ZIP replacement and previous release preserved on build failure.\n";
}
finally
{
    if (is_file($root . '/blocked.zip/keep')) unlink($root . '/blocked.zip/keep');
    if (is_dir($root . '/blocked.zip')) rmdir($root . '/blocked.zip');
    unlink($root . '/source/app.txt');
    if (is_file($root . '/release.zip')) unlink($root . '/release.zip');
    rmdir($root . '/source');
    rmdir($root);
}
