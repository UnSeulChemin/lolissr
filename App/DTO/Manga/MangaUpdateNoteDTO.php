<?php

declare(strict_types=1);

namespace App\DTO\Manga;

final class MangaUpdateNoteDTO
{
    public function __construct(
        public readonly ?int $jacquette,
        public readonly ?int $livreNote
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            jacquette: MangaNoteNormalizer::normalize($data['jacquette'] ?? null),
            livreNote: MangaNoteNormalizer::normalize($data['livre_note'] ?? null)
        );
    }

    public static function fromPost(array $post): self
    {
        return self::fromArray($post);
    }
}