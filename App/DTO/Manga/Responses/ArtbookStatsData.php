<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookStatsData
{
    public function __construct(
        public string $artbook,

        public string $thumbnailUrl,

        public string $authorLabel,
    ) {
    }
}