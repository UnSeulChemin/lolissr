<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisSearchData
{
    /**
     * @param list<ChinoisSearchItemData> $results
     */
    public function __construct(
        public array $results,
        public string $search,
    ) {
    }
}