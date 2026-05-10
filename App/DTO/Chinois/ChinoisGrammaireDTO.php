<?php

declare(strict_types=1);

namespace App\DTO\Chinois;

final readonly class ChinoisGrammaireDTO
{
    public function __construct(
        public int $id,
        public string $niveau,
        public string $titre,
        public string $structureGrammaire,
        public string $phraseChinoise,
        public string $pinyin,
        public string $traduction,
        public string $explication,
        public int $ordreAffichage,
    ) {}
}