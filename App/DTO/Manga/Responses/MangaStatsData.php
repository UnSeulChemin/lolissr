<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaStatsData
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $livre,

        public string $thumbnailUrl,
        public string $url,

        public int $numero,
        public string $numeroLabel,

        public ?int $total,
        public string $totalLabel,
    ) {
    }
}