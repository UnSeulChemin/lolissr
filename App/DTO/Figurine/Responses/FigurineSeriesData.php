<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Responses;

final readonly class FigurineSeriesData
{
    /**
     * @param list<FigurineSeriesItemData> $figurines
     */
    public function __construct(
        public array $figurines,
        public int $currentPage,
        public int $totalWaifus,
        public int $perPage,
        public int $totalPages,
    ) {
    }
}