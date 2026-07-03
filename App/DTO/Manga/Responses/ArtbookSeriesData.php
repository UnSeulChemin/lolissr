<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookSeriesData
{
    /**
     * @param list<ArtbookSeriesItemData> $artbooks
     */
    public function __construct(
        public array $artbooks,
        public int $currentPage,
        public int $totalArtbooks,
        public int $perPage,
        public int $totalPages,
    ) {
    }
}