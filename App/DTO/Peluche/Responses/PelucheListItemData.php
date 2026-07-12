<?php

declare(strict_types=1);

namespace App\DTO\Peluche\Responses;

final readonly class PelucheListItemData
{
    public function __construct(
        public string $slug,
        public int $numero,
        public string $waifu,
        public string $origin,
        public ?string $thumbnail,
        public ?string $extension,
        public ?string $thumbnailUrl,
        public bool $collect,
    ) {
    }
}