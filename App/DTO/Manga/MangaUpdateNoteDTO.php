<?php

declare(strict_types=1);

namespace App\DTO\Manga;

final class MangaUpdateNoteDTO
{
    public function __construct(
        public readonly ?int $jacquette,
        public readonly ?int $livreNote
    ) {}

    public static function fromPost(array $post): self
    {
        return new self(
            MangaNoteNormalizer::normalize($post['jacquette'] ?? null),
            MangaNoteNormalizer::normalize($post['livre_note'] ?? null)
        );
    }
}