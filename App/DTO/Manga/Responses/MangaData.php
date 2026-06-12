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

        public ?string $editeur,

        public int $numero,
        public bool $lu,

        public string $statut,

        public ?int $jacquette,
        public ?int $livreNote,
        public ?int $note,

        public ?string $commentaire,

        public ?int $total,
        public ?int $totalLu,
        public ?float $averageNote,

        public bool $xpReadRewarded,
        public bool $xpSeriesRewarded,
    ) {
    }
}