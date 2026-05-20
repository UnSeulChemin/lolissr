<?php

declare(strict_types=1);

namespace App\DTO\Upload;

final readonly class UploadThumbnailResultData
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $status,
        public ?UploadThumbnailData $data = null,
    ) {}
}