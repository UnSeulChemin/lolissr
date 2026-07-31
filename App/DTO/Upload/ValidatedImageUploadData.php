<?php

declare(strict_types=1);

namespace App\DTO\Upload;

final readonly class ValidatedImageUploadData
{
    public function __construct(
        public string $temporaryPath,
        public string $extension
    ) {
    }
}