<?php

declare(strict_types=1);

namespace App\DTO\Manga\Results;

final readonly class UpdateLuResultData
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $status,
        public int $lu,
    ) {
    }
}
