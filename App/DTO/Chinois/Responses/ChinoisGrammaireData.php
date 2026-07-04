<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisGrammaireData
{
    public function __construct(
        public int $id,

        public string $niveau,

        public string $section,

        public string $categorie,

        public string $titre,
        public string $structure,
        public ?string $abreviation,

        public string $phrase,
        public string $pinyin,
        public string $traduction,
        public ?string $explication,

        public int $position,

        public bool $maitrise,
        public bool $xpRewarded,

        public bool $hasAbreviation,
        public bool $hasExplication,

        public string $masteredClass,
        public string $masteredValue,
        public string $masteredPressed,
        public string $masteredLabel,
    ) {
    }
}
