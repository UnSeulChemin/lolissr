<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaSeriesData
{
    /**
     * @param list<MangaSeriesItemData> $mangas
     */
    public function __construct(
        public array $mangas,
        public int $compteur,
        public ?string $slugFilter,
        public int $currentPage,
        public int $totalSeries,
        public int $perPage
    ) {
    }
}
