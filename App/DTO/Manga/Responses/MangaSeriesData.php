<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaSeriesData
{
    /**
     * @param list<object> $mangas
     */
    public function __construct(
        public array $mangas,
        public ?int $compteur,
        public ?string $slugFilter,
        public int $currentPage,
    ) {}
}