<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookRepresentationData
{
    public function __construct(
        public string $title,
        public string $name,

        public string $thumbnailUrl,

        public int $total,
        public string $countLabel,
    ) {
    }
}