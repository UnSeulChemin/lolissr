<?php

declare(strict_types=1);

namespace App\DTO\Figurine\Inputs;

use Framework\Support\Str;

final readonly class FigurineCreateDTO
{
    public function __construct(
        public string $waifu,
        public int $numero,
        public string $scale,
        public ?float $height_cm,
        public string $company,
        public ?string $release_date,
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
            numero: max(1, (int) ($data['numero'] ?? 1)),
            scale: trim((string) ($data['scale'] ?? '')),
            height_cm: isset($data['height_cm']) && $data['height_cm'] !== ''
                ? (float) $data['height_cm']
                : null,
            company: trim((string) ($data['company'] ?? '')),
            release_date: Str::nullableTrim($data['release_date'] ?? null),
            slug: Str::slug((string) ($data['slug'] ?? $waifu)),
            commentaire: Str::nullableTrim($data['commentaire'] ?? null),
        );
    }
}