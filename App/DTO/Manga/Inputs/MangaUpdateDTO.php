<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use App\Core\Support\MangaNoteNormalizer;
use App\Core\Support\Str;

final class MangaUpdateDTO
{
    public function __construct(
        public readonly ?string $editeur,
        public readonly string $statut,
        public readonly ?int $jacquette,
        public readonly ?int $livreNote,
        public readonly ?string $commentaire
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(
        array $data
    ): self {
        return new self(
            editeur: Str::nullableTrim(
                $data['editeur'] ?? null
            ),
            statut: trim(
                (string) (
                    $data['statut']
                    ?? 'en_cours'
                )
            ),
            jacquette: MangaNoteNormalizer::normalize(
                $data['jacquette'] ?? null
            ),
            livreNote: MangaNoteNormalizer::normalize(
                $data['livre_note'] ?? null
            ),
            commentaire: Str::nullableTrim(
                $data['commentaire'] ?? null
            )
        );
    }

    /**
     * @param array<string, mixed> $post
     */
    public static function fromPost(
        array $post
    ): self {
        return self::fromArray($post);
    }
}
