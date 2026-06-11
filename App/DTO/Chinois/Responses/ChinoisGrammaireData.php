<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisGrammaireData
{
    public function __construct(
        public int $id,
        public string $niveau,
        public string $section,
        public int $sectionPosition,
        public string $categorie,
        public int $categoriePosition,
        public string $titre,
        public string $structure,
        public ?string $abreviation,
        public string $phrase,
        public string $pinyin,
        public string $traduction,
        public ?string $explication,
        public int $position,
        public bool $maitrise,
        public int $xpRewarded,
    ) {
    }
}