<?php

declare(strict_types=1);

namespace App\DTO\Manga;

use App\Core\Support\Str;

final class MangaCreateDTO
{
    public function __construct(
        public readonly string $livre,
        public readonly string $slug,
        public readonly int $numero,
        public readonly ?string $commentaire
    ) {}

    public static function fromPost(array $post): self
    {
        $livre = trim((string) ($post['livre'] ?? ''));

        $slug = Str::slug(
            trim((string) ($post['slug'] ?? ''))
        );

        $numero = max(
            1,
            (int) ($post['numero'] ?? 1)
        );

        $commentaire = Str::nullableTrim(
            $post['commentaire'] ?? null
        );

        return new self(
            $livre,
            $slug,
            $numero,
            $commentaire
        );
    }
}