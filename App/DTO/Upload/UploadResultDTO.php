<?php

declare(strict_types=1);

namespace App\DTO\Upload;

final readonly class UploadResultDTO
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $status,
        public ?string $thumbnail,
        public ?string $extension,
        public ?string $destination,
    ) {}
}