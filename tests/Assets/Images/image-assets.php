<?php
declare(strict_types=1);
require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
use App\Support\Media\ImageAssets;
use App\Support\Media\JpegOrientation;
use App\Support\Media\ThumbnailOptimizer;
$directory = sys_get_temp_dir() . '/oriented-images-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$publicFixture = dirname(__DIR__, 3) . '/public/images/.image-assets-test-' . bin2hex(random_bytes(8)) . '.jpg';
$versionFixture = $publicFixture . '.png';
try
{
    $image = imagecreatetruecolor(160, 80);
    $colors = [[240, 20, 20], [20, 240, 20], [20, 20, 240], [240, 240, 20]];
    foreach ($colors as $index => $color)
        imagefilledrectangle($image, ($index % 2) * 80, intdiv($index, 2) * 40,
            ($index % 2) * 80 + 79, intdiv($index, 2) * 40 + 39, imagecolorallocate($image, ...$color));
    imagejpeg($image, $directory . '/source.jpg', 100);
    imagedestroy($image);
    $bytes = (string) file_get_contents($directory . '/source.jpg');
    file_put_contents($publicFixture, $bytes);
    ThumbnailOptimizer::createGrid($publicFixture);
    $url = '/lolissr/images/' . basename($publicFixture);
    $versioned = ImageAssets::url($url . '?v=old');
    $gridUrl = ImageAssets::url($url, true);
    if (str_contains($versioned, 'v=old') || !str_contains($versioned, '?v=') || !str_contains($gridUrl, '.grid.jpg?v='))
        throw new RuntimeException('Image version or grid selection failed.');
    \Framework\Config\Config::prime(['app' => ['profiler' => true]]);
    \Framework\Debug\Profiler::startRequest();
    $reused = ImageAssets::withFingerprints(static function () use ($url): array
    {
        return [ImageAssets::url($url), ImageAssets::withFingerprints(static fn (): string => ImageAssets::url($url))];
    });
    $counters = (new ReflectionProperty(\Framework\Debug\Profiler::class, 'counters'))->getValue();
    if ($reused !== [$versioned, $versioned] || ($counters['images.fingerprint.count'] ?? 0) !== 1)
        throw new RuntimeException('Repeated image was not fingerprinted once per render.');
    ImageAssets::withFingerprints(static fn (): string => ImageAssets::url($url));
    $counters = (new ReflectionProperty(\Framework\Debug\Profiler::class, 'counters'))->getValue();
    if (($counters['images.fingerprint.count'] ?? 0) !== 2)
        throw new RuntimeException('Fingerprint cache escaped the render scope.');
    \Framework\Config\Config::clear();
    $versionBefore = $versioned;
    touch($publicFixture, time() + 10);
    clearstatcache(true, $publicFixture);
    if (ImageAssets::url($url) !== $versionBefore) throw new RuntimeException('Metadata-only change invalidated identical image content.');
    $image = imagecreatetruecolor(2, 2);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
    imagepng($image, $versionFixture, 0);
    $timestamp = time() - 60;
    touch($versionFixture, $timestamp);
    clearstatcache(true, $versionFixture);
    $versionUrl = '/lolissr/images/' . basename($versionFixture);
    $before = ImageAssets::withFingerprints(static fn (): string => ImageAssets::url($versionUrl));
    $size = filesize($versionFixture);
    imagefill($image, 0, 0, imagecolorallocate($image, 0, 0, 255));
    imagepng($image, $versionFixture, 0);
    imagedestroy($image);
    touch($versionFixture, $timestamp);
    clearstatcache(true, $versionFixture);
    if (filesize($versionFixture) !== $size || filemtime($versionFixture) !== $timestamp)
        throw new RuntimeException('Replacement fixture did not preserve size and timestamp.');
    if (ImageAssets::withFingerprints(static fn (): string => ImageAssets::url($versionUrl)) === $before)
        throw new RuntimeException('Different image content kept a stale version with identical size/date.');
    $originalHash = hash_file('sha256', $directory . '/source.jpg');
    if (!ThumbnailOptimizer::createGrid($directory . '/source.jpg')
        || hash_file('sha256', $directory . '/source.jpg') !== $originalHash
        || !is_file($directory . '/source.grid.jpg')
        || ThumbnailOptimizer::createGrid($directory . '/source.jpg'))
        throw new RuntimeException('Grid creation damaged the source or is not repeatable.');
    $small = imagecreatetruecolor(2, 2);
    imagejpeg($small, $directory . '/source.jpg', 85);
    imagedestroy($small);
    clearstatcache(true, $directory . '/source.jpg');
    if (ThumbnailOptimizer::createGrid($directory . '/source.jpg') || is_file($directory . '/source.grid.jpg'))
        throw new RuntimeException('Replacing a source retained an obsolete grid.');
    if (ThumbnailOptimizer::createGrid($directory . '/source.jpg'))
        throw new RuntimeException('A skipped conversion was retried incorrectly.');
    $orders = [1 => [0,1,2,3], 2 => [1,0,3,2], 3 => [3,2,1,0], 4 => [2,3,0,1],
        5 => [0,2,1,3], 6 => [2,0,3,1], 7 => [3,1,2,0], 8 => [1,3,0,2]];
    foreach ($orders as $orientation => $order)
    {
        foreach ([true, false] as $little)
        {
            // IFD entry: tag, SHORT, count=1, inline orientation, padding, next IFD.
            $tiff = ($little ? 'II' : 'MM') . pack($little ? 'vV' : 'nN', 42, 8)
                . pack($little ? 'vvvVvvV' : 'nnnNnnN', 1, 274, 3, 1, $orientation, 0, 0);
            $exif = "Exif\0\0" . $tiff;
            $source = substr($bytes, 0, 2) . "\xff\xe1" . pack('n', strlen($exif) + 2) . $exif . substr($bytes, 2);
            if (JpegOrientation::read($source) !== $orientation) throw new RuntimeException('EXIF parsing failed.');
            $path = $directory . '/oriented.jpg';
            file_put_contents($path, $source);
            if (!ThumbnailOptimizer::optimize($path)) throw new RuntimeException('EXIF optimization skipped.');
            $decoded = imagecreatefromjpeg($path);
            if ($decoded === false) throw new RuntimeException('Invalid oriented JPEG.');
            if (imagesx($decoded) !== ($orientation >= 5 ? 80 : 160)) throw new RuntimeException('Wrong orientation dimensions.');
            foreach ($order as $index => $expected)
            {
                $pixel = imagecolorat($decoded, ($index % 2) === 0 ? 10 : imagesx($decoded) - 11,
                    $index < 2 ? 10 : imagesy($decoded) - 11);
                $actual = [($pixel >> 16) & 255, ($pixel >> 8) & 255, $pixel & 255];
                foreach ($actual as $channel => $value)
                    if (abs($value - $colors[$expected][$channel]) > 20) throw new RuntimeException('Mirrored orientation failed: ' . $orientation);
            }
            imagedestroy($decoded);
        }
    }
    if (ImageAssets::url('/images/../.env') !== '/images/../.env') throw new RuntimeException('Unsafe image lookup.');
    if (ImageAssets::url('/not-an-image') !== '/not-an-image') throw new RuntimeException('Non-image URL changed.');
    echo "PASS: EXIF orientations, image versions, grid selection, obsolete grid removal and cached skipped conversions.\n";
}
finally
{
    foreach ([$publicFixture, $versionFixture, str_replace('.jpg', '.grid.jpg', $publicFixture)] as $fixture)
        if (is_file($fixture))
        { ThumbnailOptimizer::forgetGrid($fixture); unlink($fixture); }
    ThumbnailOptimizer::forgetGrid($directory . '/source.jpg');
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}
