<?php

declare(strict_types=1);

namespace App\DTO\Manga;

final readonly class UpdateLuResultDTO
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $status,
        public int $lu,
    ) {}
}