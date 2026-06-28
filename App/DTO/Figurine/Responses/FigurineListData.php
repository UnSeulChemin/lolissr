<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Responses;

final readonly class FigurineListData
{
    /**
     * @param list<FigurineData> $figurines
     */
    public function __construct(
        public array $figurines,
        public int $compteur,
        public int $currentPage,
        public int $totalWaifus,
        public int $perPage,
    ) {
    }
}