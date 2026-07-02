<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Responses;

final readonly class FigurineSearchData
{
    /**
     * @param list<FigurineSearchItemData> $results
     */
    public function __construct(
        public array $results,
        public string $search,
    ) {
    }
}