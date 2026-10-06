<?php

declare(strict_types=1);

namespace App\DTO\Nendoroid\Inputs;

use Framework\Support\Dates\DateNormalizer;
use Framework\Support\Strings;

final readonly class NendoroidUpdateData
{
    public function __construct(
        public string $waifu,
        public string $origin,
        public string $company,
        public ?string $release_date,
        public ?string $commentaire
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            waifu: trim((string) $data['waifu']),
            origin: trim((string) $data['origin']),
            company: trim((string) $data['company']),
            release_date: DateNormalizer::normalize(Strings::nullableTrim($data['release_date'] ?? null)),
            commentaire: Strings::nullableTrim($data['commentaire'] ?? null)
        );
    }
}