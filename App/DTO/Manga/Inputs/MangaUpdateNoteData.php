<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use App\Support\Manga\MangaNoteNormalizer;

final readonly class MangaUpdateNoteData
{
    public function __construct(
        public ?int $jacquette,
        public ?int $livreNote,
        public bool $updateJacquette = true,
        public bool $updateLivreNote = true
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            jacquette: MangaNoteNormalizer::normalize($data['jacquette'] ?? null),
            livreNote: MangaNoteNormalizer::normalize($data['livre_note'] ?? null),
            updateJacquette: array_key_exists('jacquette', $data),
            updateLivreNote: array_key_exists('livre_note', $data)
        );
    }
}
