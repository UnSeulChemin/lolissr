<?php
declare(strict_types=1);

$project = dirname(__DIR__, 3);
$fixture = sys_get_temp_dir() . '/thumbnail-delete-' . bin2hex(random_bytes(8));
define('ROOT', $fixture);
require $project . '/vendor/autoload.php';
require $project . '/Framework/Support/Helpers.php';

$directory = $fixture . '/public/images/manga/thumbnail';
mkdir($directory, 0700, true);
$sentinel = $fixture . '/public/images/manga/outside.jpg';
file_put_contents($sentinel, 'preserve');
$service = new App\Services\Media\ThumbnailUploadService(new App\Services\Media\UploadService(new App\Services\Media\ImageUploadValidator()));
$check = static function (bool $condition, string $message): void
{ if (!$condition) throw new RuntimeException($message); };
try
{
    foreach (['../outside', '..\\outside', '/outside', 'C:\\outside', 'file:stream', '.', '..', "bad\0name", "bad\nname"] as $name)
    {
        $check(!$service->remove($name, 'jpg', 'manga'), 'Unsafe thumbnail accepted');
        $check(file_get_contents($sentinel) === 'preserve', 'Outside image changed');
    }
    foreach (['../jpg', 'jpg/../../outside', 'php', 'jpg:stream', "jpg\0"] as $extension)
        $check(!$service->remove('outside', $extension, 'manga'), 'Unsafe extension accepted');
    foreach (['old-cover-01', 'Ancien nom é_日本', 'old.name-01'] as $name)
    {
        file_put_contents($directory . '/' . $name . '.jpg', 'image');
        file_put_contents($directory . '/' . $name . '.grid.jpg', 'grid');
        $check($service->remove($name, 'jpg', 'manga'), 'Historical name rejected');
        $check(!file_exists($directory . '/' . $name . '.jpg') && !file_exists($directory . '/' . $name . '.grid.jpg'), 'Image/grid cleanup failed');
        $check($service->remove($name, 'jpg', 'manga'), 'Already absent image rejected');
    }
    foreach (['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif', 'JPG'] as $extension)
    {
        file_put_contents($directory . '/format.' . $extension, 'image');
        $check($service->remove('format', $extension, 'manga'), 'Historical format rejected');
        $check(!file_exists($directory . '/format.' . $extension), 'Format image not deleted');
    }
    $check($service->remove(null, null, 'manga'), 'Empty image metadata changed');
    $check($service->remove('missing', 'jpg', 'figurine'), 'Absent collection directory changed');
    mkdir($directory . '/folder.jpg');
    $check(!$service->remove('folder', 'jpg', 'manga'), 'Directory accepted as an image');
    rmdir($directory . '/folder.jpg');
    $links = 0;
    foreach (['link.jpg', 'link.grid.jpg'] as $name)
    {
        if (!@symlink($sentinel, $directory . '/' . $name)) continue;
        $links++;
        $check(!$service->remove('link', 'jpg', 'manga'), 'Outside symlink accepted');
        $check(file_get_contents($sentinel) === 'preserve', 'Symlink target changed');
        unlink($directory . '/' . $name);
    }
    $check(file_get_contents($sentinel) === 'preserve', 'Outside sentinel damaged');
    echo 'PASS: traversal/extension rejection, historical names/formats, image+grid removal, absent files/directories; symlink checks: ' . $links . '/2.' . PHP_EOL;
}
finally
{
    // Only files explicitly staged by this fixture are removed.
    foreach (glob($directory . '/*') ?: [] as $path) if (is_file($path) || is_link($path)) unlink($path);
    unlink($sentinel);
    rmdir($directory);
    rmdir(dirname($directory));
    rmdir(dirname($directory, 2));
    rmdir(dirname($directory, 3));
    rmdir($fixture);
}
