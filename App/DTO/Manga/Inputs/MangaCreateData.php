<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use Framework\Support\Strings;

final readonly class MangaCreateData
{
    public function __construct(
        public string $slug,
        public string $livre,
        public string $editeur,
        public int $numero,
        public string $statut,
        public ?string $commentaire,
        public ?int $jacquette = null,
        public ?int $livreNote = null
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $livre = trim((string) ($data['livre'] ?? ''));

        return new self(
            slug: Strings::slug((string) ($data['slug'] ?? $livre)),
            livre: $livre,
            editeur: trim((string) ($data['editeur'] ?? '')),
            numero: max(1, (int) ($data['numero'] ?? 1)),
            statut: trim((string) ($data['statut'] ?? 'en_cours')),
            commentaire: Strings::nullableTrim($data['commentaire'] ?? null),
            jacquette: isset($data['jacquette']) && $data['jacquette'] !== '' ? (int) $data['jacquette'] : null,
            livreNote: isset($data['livre_note']) && $data['livre_note'] !== '' ? (int) $data['livre_note'] : null
        );
    }
}
