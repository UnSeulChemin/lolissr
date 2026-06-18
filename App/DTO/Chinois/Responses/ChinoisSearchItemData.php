<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisSearchItemData
{
    public function __construct(
        public int $id,
        public string $type,
        public string $titre,
        public string $description,
        public ?string $langue = null,
        public ?string $niveau = null,
    ) {
    }
}