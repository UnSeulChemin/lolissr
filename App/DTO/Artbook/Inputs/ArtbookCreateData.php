<?php

declare(strict_types=1);

namespace App\DTO\Artbook\Inputs;

use Framework\Support\Dates\DateNormalizer;
use Framework\Support\Strings;

final readonly class ArtbookCreateData
{
    public function __construct(
        public string $artbook,
        public ?string $auteur,
        public ?string $serie,
        public string $company,
        public ?string $release_date,
        public ?string $commentaire,
        public string $slug,
        public int $numero
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $artbook = trim((string) ($data['artbook'] ?? ''));

        $typeSource = (string) ($data['type_source'] ?? '');
        $source = Strings::nullableTrim($data['source'] ?? null);

        $auteur = $typeSource === 'auteur'
            ? $source
            : null;

        $serie = $typeSource === 'serie'
            ? $source
            : null;

        return new self(
            artbook: $artbook,
            auteur: $auteur,
            serie: $serie,
            company: trim((string) ($data['company'] ?? '')),
            release_date: DateNormalizer::normalize(Strings::nullableTrim($data['release_date'] ?? null)),
            commentaire: Strings::nullableTrim($data['commentaire'] ?? null),
            slug: Strings::slug((string) ($data['slug'] ?? $artbook)),
            numero: max(1, (int) ($data['numero'] ?? 1))
        );
    }
}