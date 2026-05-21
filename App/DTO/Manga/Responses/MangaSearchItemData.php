<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaSearchItemData
{
    public function __construct(
        public string $slug,
        public int $numero,
        public string $livre,
        public ?string $thumbnailPath,
        public ?string $thumbnailExtension,
        public ?int $note,
    ) {
    }
}