<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaSearchItemData
{
    public function __construct(
        public string $slug,
        public int $numero,
        public string $livre,
        public string $thumbnail,
        public string $extension,
        public ?int $note,
        public bool $lu
    ) {
    }
}
