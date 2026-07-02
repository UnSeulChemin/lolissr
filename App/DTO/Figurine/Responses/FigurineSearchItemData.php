<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Responses;

final readonly class FigurineSearchItemData
{
    public function __construct(
        public string $slug,
        public int $numero,
        public string $origin,
        public string $waifu,
        public ?string $thumbnail,
        public ?string $extension,
    ) {
    }
}