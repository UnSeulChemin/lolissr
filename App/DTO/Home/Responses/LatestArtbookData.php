<?php

declare(strict_types=1);

namespace App\DTO\Home\Responses;

final readonly class LatestArtbookData
{
    public function __construct(
        public string $artbook,
        public ?string $auteur,
        public string $thumbnail,
        public ?string $extension
    ) {
    }
}
