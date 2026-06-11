<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use Framework\Support\MangaNoteNormalizer;

final readonly class MangaUpdateNoteDTO
{
    public function __construct(
        public ?int $jacquette,
        public ?int $livreNote,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            jacquette: MangaNoteNormalizer::normalize($data['jacquette'] ?? null),
            livreNote: MangaNoteNormalizer::normalize($data['livre_note'] ?? null),
        );
    }
}