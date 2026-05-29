<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaSearchData
{
    /**
     * @param list<MangaSearchItemData> $mangas
     */
    public function __construct(
        public array $mangas,
        public string $search,
    ) {
    }
}
