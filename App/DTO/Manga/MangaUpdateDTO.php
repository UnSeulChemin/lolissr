<?php

declare(strict_types=1);

namespace App\DTO\Manga;

use App\Core\Support\Str;

final class MangaUpdateDTO
{
    public function __construct(
        public readonly ?int $jacquette,
        public readonly ?int $livreNote,
        public readonly ?string $commentaire
    ) {}

    public static function fromPost(array $post): self
    {
        return new self(
            MangaNoteNormalizer::normalize($post['jacquette'] ?? null),
            MangaNoteNormalizer::normalize($post['livre_note'] ?? null),
            Str::nullableTrim($post['commentaire'] ?? null)
        );
    }
}