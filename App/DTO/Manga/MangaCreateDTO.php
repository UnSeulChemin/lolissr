<?php

declare(strict_types=1);

namespace App\DTO\Manga;

use App\Core\Support\Str;

final class MangaCreateDTO
{
    public function __construct(
        public readonly string $livre,
        public readonly ?string $editeur,
        public readonly string $statut,
        public readonly string $slug,
        public readonly int $numero,
        public readonly ?string $commentaire
    ) {}

    public static function fromArray(array $data): self
    {
        $livre = trim((string) ($data['livre'] ?? ''));

        return new self(
            livre: $livre,
            editeur: Str::nullableTrim($data['editeur'] ?? null),
            statut: trim((string) ($data['statut'] ?? 'en_cours')),
            slug: Str::slug((string) ($data['slug'] ?? $livre)),
            numero: max(1, (int) ($data['numero'] ?? 1)),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null)
        );
    }

    public static function fromPost(array $post): self
    {
        return self::fromArray($post);
    }
}