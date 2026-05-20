<?php

declare(strict_types=1);

namespace App\DTO\Upload;

final readonly class UploadThumbnailData
{
    public function __construct(
        public string $thumbnail,
        public string $extension,
        public string $destination,
    ) {}
}