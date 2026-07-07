<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use Framework\Support\DateNormalizer;
use Framework\Support\Str;

final readonly class ArtbookUpdateDTO
{
    public function __construct(
        public string $artbook,
        public ?string $auteur,
        public ?string $serie,
        public string $company,
        public ?string $release_date,
        public ?string $commentaire,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $typeSource = (string) ($data['type_source'] ?? '');
        $source = Str::nullableTrim($data['source'] ?? null);

        $auteur = $typeSource === 'auteur'
            ? $source
            : null;

        $serie = $typeSource === 'serie'
            ? $source
            : null;

        return new self(
            artbook: trim((string) ($data['artbook'] ?? '')),
            auteur: $auteur,
            serie: $serie,
            company: trim((string) ($data['company'] ?? '')),
            release_date: DateNormalizer::normalize(
                Str::nullableTrim($data['release_date'] ?? null),
            ),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}