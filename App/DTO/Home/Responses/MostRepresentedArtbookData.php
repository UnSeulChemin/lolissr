<?php

declare(strict_types=1);

namespace App\DTO\Home\Responses;

final readonly class MostRepresentedArtbookData
{
    public function __construct(
        public string $type,
        public string $name,
        public int $total,
        public string $thumbnail,
        public ?string $extension
    ) {
    }
}
