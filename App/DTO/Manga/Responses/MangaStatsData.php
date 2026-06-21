<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaStatsData
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $livre,
        public ?string $thumbnail,
        public ?string $extension,
        public int $numero,
        public ?int $total
    ) {
    }
}
