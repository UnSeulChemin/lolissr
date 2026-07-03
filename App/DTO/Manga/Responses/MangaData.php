<?php

declare(strict_types=1);

namespace App\DTO\Manga\Responses;

final readonly class MangaData
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $livre,

        public ?string $thumbnail,
        public ?string $extension,
        public ?string $thumbnailUrl,

        public string $editeur,
        public bool $hasEditeur,

        public int $numero,
        public bool $lu,

        public string $statut,
        public string $statusLabel,

        public ?int $jacquette,
        public ?int $livreNote,
        public ?int $note,

        public bool $isPerfectJacquette,
        public bool $isPerfectLivre,

        public ?string $commentaire,
        public bool $hasCommentaire,

        public ?int $total,
        public ?int $totalLu,
        public ?float $averageNote,

        public bool $xpReadRewarded,
        public bool $xpSeriesRewarded,
    ) {
    }
}