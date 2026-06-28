<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Inputs;

use Framework\Support\Str;

final readonly class FigurineCreateDTO
{
    public function __construct(
        public string $waifu,
        public string $company,
        public string $slug,
        public ?string $commentaire
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $waifu = trim((string) ($data['waifu'] ?? ''));

        return new self(
            waifu: $waifu,
            company: trim((string) ($data['company'] ?? '')),
            slug: Str::slug((string) ($data['slug'] ?? $waifu)),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}