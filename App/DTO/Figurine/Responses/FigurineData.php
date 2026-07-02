<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Responses;

final readonly class FigurineData
{
    public function __construct(
        public int $id,

        public string $slug,
        public int $numero,

        public string $waifu,
        public string $scale,
        public ?string $height_cm,

        public string $company,
        public ?string $release_date,

        public string $thumbnail,
        public string $extension,

        public ?string $commentaire,
    ) {
    }
}