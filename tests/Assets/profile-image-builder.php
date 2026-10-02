<?php

declare(strict_types=1);
require dirname(__DIR__, 2) . '/scripts/Support/ProfileImageBuilder.php';
$source = tempnam(sys_get_temp_dir(), 'profile-source-');
$target = $source . '.webp';
if ($source === false) throw new RuntimeException('Cannot create fixture');
try
{
    $image = imagecreatetruecolor(128, 128);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    for ($y = 0; $y < 128; $y++) for ($x = 0; $x < 128; $x++)
        imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, ($x * 13) % 256, ($y * 17) % 256, ($x * $y) % 256, $x < 64 ? 127 : 40));
    imagepng($image, $source);
    imagedestroy($image);
    if (!ProfileImageBuilder::prepare($source, $target, 32)) throw new RuntimeException('Conversion skipped');
    $converted = imagecreatefromwebp($target);
    if ($converted === false || imagesx($converted) !== 32 || imagesy($converted) !== 32
        || (imagecolorat($converted, 0, 0) >> 24) !== 127
        || (imagecolorat($converted, 30, 30) >> 24) !== 40)
        throw new RuntimeException('Dimensions or alpha changed incorrectly');
    imagedestroy($converted);
    $hash = hash_file('sha256', $target);
    // Simulate an interruption after publishing, before source removal/DB update.
    if (!ProfileImageBuilder::prepare($source, $target, 32) || hash_file('sha256', $target) !== $hash)
        throw new RuntimeException('Conversion cannot resume');
    ProfileImageBuilder::prepare($target, $target, 32);
    if (hash_file('sha256', $target) !== $hash) throw new RuntimeException('Repeated resize changed output');
    file_put_contents($target, 'unrelated existing file');
    try
    {
        ProfileImageBuilder::prepare($source, $target, 32);
        throw new RuntimeException('Collision was accepted');
    }
    catch (RuntimeException $error)
    {
        if (!str_starts_with($error->getMessage(), 'Different image already exists:')) throw $error;
    }
    if (file_get_contents($target) !== 'unrelated existing file' || !is_file($source))
        throw new RuntimeException('Collision damaged existing files');
    echo "PASS: resize, transparency, interrupted conversion recovery, repeated run and collision protection.\n";
}
finally
{
    if (is_file($source)) unlink($source);
    if (is_file($target)) unlink($target);
}
