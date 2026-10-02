<?php
declare(strict_types=1);

// Only HTTP upload provenance/move are simulated. Content validation and disk cleanup are real.
namespace App\Services\Media {
    function is_uploaded_file(string $path): bool { return is_file($path); }
    function move_uploaded_file(string $from, string $to): bool { return rename($from, $to); }
}
namespace {
    require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';
    use App\Services\Media\ImageUploadValidator;
    use App\Services\Media\UploadService;
    use Framework\Config\Config;

    $dir = sys_get_temp_dir() . '/media-integrity-' . bin2hex(random_bytes(8));
    mkdir($dir, 0700);
    Config::prime(['app' => ['env' => 'local'], 'log' => ['enabled' => false], 'upload' => [
        'allowed_extensions' => ['jpg', 'png', 'webp'],
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ]]);
    $check = static function (bool $condition, string $message): void {
        if (!$condition) throw new \RuntimeException($message);
    };
    $validator = new ImageUploadValidator();
    $uploadService = new UploadService($validator);
    try {
        $image = imagecreatetruecolor(2, 2);
        imagepng($image, $dir . '/source.png');
        imagejpeg($image, $dir . '/source.jpg');
        imagewebp($image, $dir . '/source.webp');
        imagedestroy($image);
        foreach (['png', 'jpg', 'webp'] as $actual) {
            foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
                $path = $dir . '/source.' . $actual;
                $result = $validator->validate(['image' => ['name' => 'image.' . $extension,
                    'tmp_name' => $path, 'size' => filesize($path), 'error' => UPLOAD_ERR_OK]]);
                $matches = $actual === ($extension === 'jpeg' ? 'jpg' : $extension);
                $check($matches ? $result instanceof \App\DTO\Upload\ValidatedImageUploadData
                    : $result instanceof \App\DTO\Common\ServiceResult && !$result->success && $result->status === 422,
                    "Wrong MIME/extension result: $actual/$extension");
            }
        }
        $uploads = [];
        for ($i = 0; $i < 2; $i++) {
            $path = $dir . '/incoming.png';
            copy($dir . '/source.png', $path);
            $result = $uploadService->uploadThumbnail(str_repeat('漢', 150), 1, $dir, ['image' => [
                'name' => 'image.png', 'tmp_name' => $path, 'size' => filesize($path), 'error' => UPLOAD_ERR_OK,
            ]]);
            $check($result->success, 'Same-name upload failed.');
            $uploads[] = $result->data['upload'];
        }
        $check($uploads[0]->destinationPath !== $uploads[1]->destinationPath, 'Recreated image reused old path.');
        $check(strlen(basename($uploads[0]->destinationPath)) <= 255, 'Filename exceeds filesystem limit.');
        $check($uploadService->removeFile($uploads[0]->destinationPath), 'Old image cleanup failed.');
        $check(is_file($uploads[1]->destinationPath), 'Old cleanup removed replacement image.');
    } finally {
        foreach (glob($dir . '/*') ?: [] as $file) unlink($file);
        rmdir($dir);
        Config::clear();
    }
    echo "PASS: MIME/extension matrix, JPEG alias, distinct upload names and replacement image preservation.\n";
}
