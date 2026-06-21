<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class ArtbookData
{
    public function __construct(
        public int $id,

        public string $thumbnail,
        public string $extension,

        public string $slug,
        public int $numero,

        public string $artbook,

        public ?string $auteur,
        public ?string $serie,

        public string $createdAt,
    ) {
    }
}
