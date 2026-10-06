<?php

declare(strict_types=1);

namespace App\DTO\Artbook\Inputs;

use Framework\Support\Dates\DateNormalizer;
use Framework\Support\Strings;

final readonly class ArtbookUpdateData
{
    public function __construct(
        public string $artbook,
        public string $source,
        public string $company,
        public ?string $release_date,
        public ?string $commentaire
    )
    {
    }

    // =================================================
    // FABRICATION
    // =================================================

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            artbook: trim((string) ($data['artbook'] ?? '')),
            source: trim((string) ($data['source'] ?? '')),
            company: trim((string) ($data['company'] ?? '')),
            release_date: DateNormalizer::normalize(
                Strings::nullableTrim(is_string($data['release_date'] ?? null) ? $data['release_date'] : null)
            ),
            commentaire: Strings::nullableTrim(is_string($data['commentaire'] ?? null) ? $data['commentaire'] : null)
        );
    }
}