<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaShowData
{
    public function __construct(
        public MangaData $manga,
        public string $canonicalSlug,
    ) {
    }
}