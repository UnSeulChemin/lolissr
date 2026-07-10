<?php

declare(strict_types=1);

namespace App\DTO\Nendoroid\Responses;

final readonly class NendoroidSearchItemData
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