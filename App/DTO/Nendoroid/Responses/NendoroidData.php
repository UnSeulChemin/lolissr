<?php

declare(strict_types=1);

namespace App\DTO\Nendoroid\Responses;

final readonly class NendoroidData
{
    public function __construct(
        public int $id,

        public string $slug,
        public int $numero,

        public string $waifu,
        public string $company,

        public string $thumbnail,
        public string $extension,

        public ?string $commentaire,
    ) {
    }
}