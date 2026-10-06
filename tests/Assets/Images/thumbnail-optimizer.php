<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';
use App\Support\Media\ThumbnailOptimizer;

$directory = sys_get_temp_dir() . '/thumbnail-test-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$check = static function (bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
};
try
{
    $image = imagecreatetruecolor(2400, 1600);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 50, 100, 200, 40));
    for ($y = 0; $y < 1600; $y += 8) for ($x = 0; $x < 2400; $x += 8)
        imagefilledrectangle($image, $x, $y, $x + 7, $y + 7,
            imagecolorallocatealpha($image, random_int(0, 255), random_int(0, 255), random_int(0, 255), 40));
    imagepng($image, $directory . '/cover.png');
    imagejpeg($image, $directory . '/cover.jpg', 100);
    imagewebp($image, $directory . '/cover.webp', IMG_WEBP_LOSSLESS);
    imagedestroy($image);
    $memoryLimit = ini_get('memory_limit');
    $originalHash = hash_file('sha256', $directory . '/cover.png');
    try
    {
        ini_set('memory_limit', (string) (memory_get_usage(true) + 8388608));
        $check(!ThumbnailOptimizer::optimize($directory . '/cover.png', 600)
            && hash_file('sha256', $directory . '/cover.png') === $originalHash, 'Memory budget ignored.');
    }
    finally
    {
        ini_set('memory_limit', $memoryLimit);
    }
    foreach (['png' => IMAGETYPE_PNG, 'jpg' => IMAGETYPE_JPEG, 'webp' => IMAGETYPE_WEBP] as $extension => $type)
    {
        $path = $directory . '/cover.' . $extension;
        $before = filesize($path);
        $check(ThumbnailOptimizer::optimize($path, 600), 'Optimization skipped: ' . $extension);
        clearstatcache(true, $path);
        $info = getimagesize($path);
        $check($info !== false && $info[0] === 600 && $info[1] === 400 && $info[2] === $type, 'Wrong dimensions/format.');
        $check(filesize($path) < $before, 'No transfer savings.');
        if ($extension !== 'jpg')
        {
            $decoded = imagecreatefromstring((string) file_get_contents($path));
            $check($decoded !== false && (imagecolorat($decoded, 10, 10) >> 24) === 40, 'Alpha lost.');
            imagedestroy($decoded);
        }
    }
    $path = $directory . '/cover.png';
    $bytes = (string) file_get_contents($path);
    $chunk = 'acTL' . pack('NN', 2, 0);
    $animated = substr($bytes, 0, 33) . pack('N', 8) . $chunk . pack('N', crc32($chunk)) . substr($bytes, 33);
    file_put_contents($path, $animated);
    $check(!ThumbnailOptimizer::optimize($path, 100) && file_get_contents($path) === $animated, 'Animation changed.');
    $path = $directory . '/cover.jpg';
    $bytes = (string) file_get_contents($path);
    $exif = substr($bytes, 0, 2) . "\xff\xe1" . pack('n', 8) . "Exif\0\0" . substr($bytes, 2);
    file_put_contents($path, $exif);
    $check(!ThumbnailOptimizer::optimize($path, 100) && file_get_contents($path) === $exif, 'EXIF image changed.');
    $check(count(glob($directory . '/.image-*') ?: []) === 0, 'Staging files leaked.');
    echo "PASS: smaller thumbnails, dimensions, formats, alpha, animation and EXIF preservation.\n";
}
finally
{
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}
