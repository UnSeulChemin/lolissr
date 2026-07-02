<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Inputs;

use Framework\Support\Str;

final readonly class FigurineUpdateDTO
{
    public function __construct(
        public ?string $scale,
        public ?float $height_cm,
        public ?string $company,
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
            scale: Str::nullableTrim($data['scale'] ?? null),
            height_cm: isset($data['height_cm']) && $data['height_cm'] !== ''
                ? (float) $data['height_cm']
                : null,
            company: Str::nullableTrim($data['company'] ?? null),
            release_date: Str::nullableTrim($data['release_date'] ?? null),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}