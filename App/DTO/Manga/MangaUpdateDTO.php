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

    public static function fromArray(array $data): self
    {
        return new self(
            jacquette: MangaNoteNormalizer::normalize($data['jacquette'] ?? null),
            livreNote: MangaNoteNormalizer::normalize($data['livre_note'] ?? null),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null)
        );
    }

    public static function fromPost(array $post): self
    {
        return self::fromArray($post);
    }
}