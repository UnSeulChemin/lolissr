<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaSeriesItemData
{
    public function __construct(
        public string $slug,
        public int $numero,
        public string $livre,
        public ?string $thumbnail,
        public ?string $extension,
        public string $statut,
        public ?float $note,
        public ?float $averageNote,
        public int $total,
        public int $totalLu,
        public bool $lu
    ) {
    }
}
