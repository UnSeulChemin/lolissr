<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Inputs;

use Framework\Support\Str;
use Framework\Support\DateNormalizer;

final readonly class FigurineUpdateDTO
{
    public function __construct(
        public string $waifu,
        public string $origin,
        public string $scale,
        public ?float $height_cm,
        public string $company,
        public ?string $release_date,
        public ?string $commentaire
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            waifu: trim((string) $data['waifu']),
            origin: trim((string) $data['origin']),
            scale: trim((string) $data['scale']),
            height_cm: isset($data['height_cm']) && $data['height_cm'] !== ''
                ? (float) $data['height_cm']
                : null,
            company: trim((string) $data['company']),
            release_date: DateNormalizer::normalize(
                Str::nullableTrim($data['release_date'] ?? null),
            ),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}
