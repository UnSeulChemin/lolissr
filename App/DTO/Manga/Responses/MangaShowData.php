<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

use App\Models\Manga;

final readonly class MangaShowData
{
    public function __construct(
        public Manga $manga,
        public string $canonicalSlug,
    ) {
    }
}