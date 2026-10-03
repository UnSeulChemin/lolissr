<?php

declare(strict_types=1);

namespace App\DTO\Peluche\Responses;

final readonly class PelucheData
{
    public function __construct(
        public int $id,

        public string $slug,
        public int $numero,

        public string $waifu,
        public string $origin,
        public string $company,

        public bool $collect,

        public ?string $release_date,

        public ?string $thumbnail,
        public ?string $extension,

        public ?string $thumbnailUrl,

        public ?string $commentaire,

        public bool $xpCollectRewarded
    )
    {
    }
}