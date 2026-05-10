<?php

declare(strict_types=1);

namespace App\DTO\Chinois;

final readonly class ChinoisGrammaireDTO
{
    public function __construct(
        public int $id,
        public string $niveau,
        public string $titre,
        public string $structure,
        public string $phrase,
        public string $pinyin,
        public string $traduction,
        public string $explication,
        public int $position,
    ) {}
}