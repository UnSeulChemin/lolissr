<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookListData
{
    /**
     * @param list<ArtbookData> $artbooks
     */
    public function __construct(
        public array $artbooks,
        public int $compteur,
        public int $currentPage,
        public int $totalArtbooks,
        public int $perPage,
    ) {
    }
}