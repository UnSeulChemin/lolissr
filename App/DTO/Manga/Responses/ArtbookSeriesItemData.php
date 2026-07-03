<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookSeriesItemData
{
    public function __construct(
        public string $slug,
        public int $numero,

        public string $artbook,

        public ?string $thumbnail,
        public ?string $extension,
        public ?string $thumbnailUrl,

        public ?string $auteur,
        public ?string $serie,

        public string $subtitle,
    ) {
    }
}