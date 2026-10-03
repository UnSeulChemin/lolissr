<?php

declare(strict_types=1);

namespace App\DTO\Artbook\Responses;

final readonly class ArtbookListData
{
    /**
     * @param list<ArtbookListItemData> $artbooks
     */
    public function __construct(
        public array $artbooks,
        public int $currentPage,
        public int $totalArtbooks,
        public int $perPage,
        public int $totalPages
    )
    {
    }
}