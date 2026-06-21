<?php

declare(strict_types=1);

namespace App\DTO\Upload;

final readonly class UploadThumbnailData
{
    public function __construct(
        public string $thumbnailPath,
        public string $extension,
        public string $destinationPath
    ) {
    }
}
