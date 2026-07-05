<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use Framework\Support\Str;

final readonly class MangaCreateDTO
{
    public function __construct(
        public string $slug,
        public string $livre,
        public string $editeur,
        public int $numero,
        public string $statut,
        public ?string $commentaire,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $livre = trim((string) ($data['livre'] ?? ''));

        return new self(
            slug: Str::slug((string) ($data['slug'] ?? $livre)),
            livre: $livre,
            editeur: trim((string) ($data['editeur'] ?? '')),
            numero: max(1, (int) ($data['numero'] ?? 1)),
            statut: trim((string) ($data['statut'] ?? 'en_cours')),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}
