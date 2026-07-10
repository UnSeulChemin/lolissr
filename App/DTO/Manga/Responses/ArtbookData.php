<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookData
{
    public function __construct(
        public int $id,

        public string $slug,
        public int $numero,

        public bool $lu,

        public string $artbook,

        public ?string $thumbnail,
        public ?string $extension,
        public ?string $thumbnailUrl,

        public ?string $auteur,
        public bool $hasAuteur,

        public ?string $serie,
        public bool $hasSerie,

        public string $company,

        public ?string $releaseDate,

        public ?string $commentaire,
        public bool $hasCommentaire,

        public string $createdAt,
    ) {
    }
}