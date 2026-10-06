<?php
declare(strict_types=1);
require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
require __DIR__ . '/Support/short-manifest-write.php';
use App\Support\Media\ImageAssets;
use App\Support\Media\JpegOrientation;
use App\Support\Media\ThumbnailOptimizer;
$directory = sys_get_temp_dir() . '/oriented-images-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$publicFixture = dirname(__DIR__, 3) . '/public/images/.image-assets-test-' . bin2hex(random_bytes(8)) . '.jpg';
$versionFixture = $publicFixture . '.png';
try
{
    $shortPath = $directory . '/.short-manifest-test.json';
    file_put_contents($shortPath, '{"version":"original"}');
    $failed = false;
    try
    { \App\Support\Media\ImageManifest::write($shortPath, ['version' => 'complete']); }
    catch (RuntimeException)
    { $failed = true; }
    if (!$failed || file_get_contents($shortPath) !== '{"version":"original"}' || count(glob($directory . '/.short-manifest-*') ?: []) !== 1)
        throw new RuntimeException('Partial manifest publication damaged the previous file or left staging files.');
    unlink($shortPath);
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
    ImageAssets::forgetFingerprint($publicFixture);
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
    if (($counters['images.fingerprint.count'] ?? 0) !== 1 || ($counters['images.fingerprint.hit'] ?? 0) !== 1)
        throw new RuntimeException('Persistent fingerprint was not reused across renders.');
    $code = 'require ' . var_export(dirname(__DIR__, 3) . '/tests/Support/bootstrap.php', true) . '; '
        . '\\Framework\\Config\\Config::prime(["app" => ["profiler" => true]]); \\Framework\\Debug\\Profiler::startRequest(); '
        . '$url = \\App\\Support\\Media\\ImageAssets::url(' . var_export($url, true) . '); '
        . '$counts = (new ReflectionProperty(\\Framework\\Debug\\Profiler::class, "counters"))->getValue(); '
        . 'echo json_encode([$url, $counts["images.fingerprint.count"] ?? 0]);';
    $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start fingerprint persistence test.');
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0 || json_decode($output, true) !== [$versioned, 0])
        throw new RuntimeException('Fresh process did not reuse persisted fingerprint: ' . $errors);
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
    ImageAssets::refreshFingerprint($versionFixture);
    if (ImageAssets::withFingerprints(static fn (): string => ImageAssets::url($versionUrl)) === $before)
        throw new RuntimeException('Different image content kept a stale version with identical size/date.');
    $entry = dirname(__DIR__, 3) . '/storage/image-fingerprints/' . hash('sha256', realpath($versionFixture)) . '.json';
    file_put_contents($entry, '{broken');
    if (!str_contains(ImageAssets::url($versionUrl), 'v=' . hash_file('sha256', $versionFixture)))
        throw new RuntimeException('Corrupt fingerprint manifest did not fall back to content hashing.');
    $gridFixture = str_replace('.jpg', '.grid.jpg', $publicFixture);
    file_put_contents($gridFixture, 'manual-grid-replacement');
    ImageAssets::refreshFingerprint($gridFixture);
    if (!str_contains(ImageAssets::url($url, true), 'v=' . hash_file('sha256', $gridFixture)))
        throw new RuntimeException('Grid replacement did not refresh its independent fingerprint.');
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
    echo "PASS: EXIF orientations, persistent fingerprints across processes, manual refresh, corrupt manifest fallback, independent grid versions and obsolete grid removal.\n";
}
finally
{
    foreach ([$publicFixture, $versionFixture, str_replace('.jpg', '.grid.jpg', $publicFixture)] as $fixture)
        if (is_file($fixture))
        { ImageAssets::forgetFingerprint($fixture); ThumbnailOptimizer::forgetGrid($fixture); unlink($fixture); }
    ThumbnailOptimizer::forgetGrid($directory . '/source.jpg');
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}
